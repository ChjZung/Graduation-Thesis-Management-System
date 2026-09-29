<?php

namespace App\Http\Controllers\TruongKhoa;

use App\Http\Controllers\Controller;
use App\Models\Khoa;
use App\Models\BoMon;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\HocKy;
use App\Models\PhanCongPhanBien;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DuyetDeTaiController extends Controller
{
    private function getKhoa()
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if ($gv) {
            $gv->loadMissing('boMon.khoa');
            if ($gv->boMon && $gv->boMon->khoa) {
                return $gv->boMon->khoa;
            }
        }
        return Khoa::first();
    }

    public function index(Request $request)
    {
        $khoa = $this->getKhoa();
        $boMons = BoMon::orderBy('TenBoMon')->get();
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();

        $query = DeTai::with(['giangVien.boMon', 'hocKy', 'nganh', 'phanCongPhanBiens.giangVien']);

        if ($request->filled('TrangThai') && $request->TrangThai !== 'ALL') {
            $query->where('TrangThai', $request->TrangThai);
        } elseif (!$request->filled('TrangThai')) {
            $query->where('TrangThai', 'Chờ duyệt cấp Khoa');
        }

        if ($request->filled('MaBoMon')) {
            $maBM = $request->MaBoMon;
            $query->whereHas('giangVien', fn($g) => $g->where('MaBoMon', $maBM));
        }

        if ($request->filled('MaHocKy')) {
            $query->where('MaHocKy', $request->MaHocKy);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('TenDeTai', 'like', "%{$s}%")
                  ->orWhere('MaDeTai', 'like', "%{$s}%")
                  ->orWhereHas('giangVien', fn($g) => $g->where('HoTen', 'like', "%{$s}%"));
            });
        }

        $detais = $query->orderBy('created_at', 'desc')->paginate(5)->withQueryString();

        $counts = [
            'cho_duyet_khoa'      => DeTai::where('TrangThai', 'Chờ duyệt cấp Khoa')->count(),
            'truong_khoa_da_duyet'=> DeTai::where('TrangThai', 'Trưởng khoa đã duyệt')->count(),
            'da_cong_bo'          => DeTai::where('TrangThai', 'Đã công bố')->count(),
            'cho_duyet_bm'        => DeTai::where('TrangThai', 'Chờ duyệt cấp Bộ môn')->count(),
            'dang_phan_bien'      => DeTai::where('TrangThai', 'Đang phản biện đề cương')->count(),
            'yeu_cau_sua'         => DeTai::where('TrangThai', 'Yêu cầu chỉnh sửa')->count(),
            'tu_choi'             => DeTai::where('TrangThai', 'Từ chối')->count(),
            'total'               => DeTai::count(),
        ];

        return view('truongkhoa.duyet_detai.index', compact('khoa', 'detais', 'counts', 'boMons', 'hocKies'));
    }

    public function show($id)
    {
        $khoa = $this->getKhoa();
        $detai = DeTai::with(['giangVien.boMon', 'hocKy', 'nganh', 'phanCongPhanBiens.giangVien'])
            ->findOrFail($id);

        $phanBienHienTai = $detai->phanCongPhanBiens->firstWhere('VaiTro', 'Phản biện đề cương');

        return view('truongkhoa.duyet_detai.show', compact('khoa', 'detai', 'phanBienHienTai'));
    }

    public function approve(Request $request, $id)
    {
        $user = Auth::user();
        $gv = GiangVien::where('MaTK', $user->MaTK)->first();

        $detai = DeTai::findOrFail($id);

        $detai->update([
            'TrangThai'       => 'Trưởng khoa đã duyệt',
            'NgayDuyetKhoa'   => now(),
            'NguoiDuyetKhoa'  => $gv ? $gv->MaGV : 'TK',
            'LyDoTuChoi'      => null,
        ]);

        // Gửi thông báo cho GV đề xuất
        if ($detai->giangVien && $detai->giangVien->MaTK) {
            ThongBaoService::guiDen(
                $detai->giangVien->MaTK,
                '🎉 Đề tài đã được Trưởng khoa phê duyệt chính thức',
                "Đề tài '{$detai->TenDeTai}' đã được Trưởng khoa phê duyệt và đang chờ Giáo vụ Khoa công bố cho sinh viên đăng ký.",
                'Đề tài'
            );
        }

        return redirect()->back()->with('success', "Đã phê duyệt đề tài '{$detai->TenDeTai}' cấp Khoa thành công! Đề tài sẵn sàng để Giáo vụ công bố.");
    }

    public function requestEdit(Request $request, $id)
    {
        $request->validate([
            'YeuCauSua' => 'required|string|max:1000',
        ], [
            'YeuCauSua.required' => 'Vui lòng nhập nội dung yêu cầu điều chỉnh.',
        ]);

        $detai = DeTai::findOrFail($id);

        $detai->update([
            'TrangThai'  => 'Yêu cầu chỉnh sửa',
            'LyDoTuChoi' => trim($request->YeuCauSua),
        ]);

        if ($detai->giangVien && $detai->giangVien->MaTK) {
            ThongBaoService::guiDen(
                $detai->giangVien->MaTK,
                '⚠️ Yêu cầu chỉnh sửa đề tài từ Trưởng khoa',
                "Đề tài '{$detai->TenDeTai}' có ý kiến chỉnh sửa từ Trưởng khoa: " . trim($request->YeuCauSua),
                'Đề tài'
            );
        }

        return redirect()->back()->with('success', "Đã gửi yêu cầu chỉnh sửa đề tài tới Giảng viên.");
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'LyDoTuChoi' => 'required|string|max:1000',
        ], [
            'LyDoTuChoi.required' => 'Vui lòng nhập lý do từ chối.',
        ]);

        $detai = DeTai::findOrFail($id);

        $detai->update([
            'TrangThai'  => 'Từ chối',
            'LyDoTuChoi' => trim($request->LyDoTuChoi),
        ]);

        if ($detai->giangVien && $detai->giangVien->MaTK) {
            ThongBaoService::guiDen(
                $detai->giangVien->MaTK,
                '❌ Đề tài bị Trưởng khoa từ chối',
                "Đề tài '{$detai->TenDeTai}' đã bị Trưởng khoa từ chối phê duyệt. Lý do: " . trim($request->LyDoTuChoi),
                'Đề tài'
            );
        }

        return redirect()->back()->with('success', "Đã từ chối đề tài '{$detai->TenDeTai}'.");
    }
}
