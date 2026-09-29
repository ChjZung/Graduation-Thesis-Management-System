<?php

namespace App\Http\Controllers\TruongBoMon;

use App\Http\Controllers\Controller;
use App\Models\BoMon;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\HocKy;
use App\Models\PhanCongPhanBien;
use App\Helpers\IdGenerator;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DuyetDeTaiController extends Controller
{
    private function getBoMon()
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if ($gv && $gv->MaBoMon) {
            return BoMon::where('MaBoMon', $gv->MaBoMon)->first() ?? BoMon::first();
        }
        return BoMon::where('TruongBoMon', 'like', '%' . ($gv->HoTen ?? '') . '%')->first() ?? BoMon::first();
    }

    public function index(Request $request)
    {
        $boMon = $this->getBoMon();
        $maBoMon = $boMon ? $boMon->MaBoMon : 'CNPM';

        $giangViens = GiangVien::where('MaBoMon', $maBoMon)->get();
        $gvIds = $giangViens->pluck('MaGV');

        // Toàn bộ GV trong trường (hoặc cùng khoa) để phân công phản biện
        $allGiangViens = GiangVien::orderBy('HoTen')->get();

        $query = DeTai::with(['giangVien', 'hocKy', 'nganh', 'phanCongPhanBiens.giangVien'])
            ->whereIn('MaGV', $gvIds);

        if ($request->filled('TrangThai') && $request->TrangThai !== 'ALL') {
            $query->where('TrangThai', $request->TrangThai);
        } elseif (!$request->filled('TrangThai')) {
            $query->whereIn('TrangThai', ['Chờ duyệt cấp Bộ môn', 'Đang phản biện đề cương', 'Đã phản biện - Chờ duyệt BM']);
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
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();

        $counts = [
            'cho_duyet_bm'   => DeTai::whereIn('MaGV', $gvIds)->where('TrangThai', 'Chờ duyệt cấp Bộ môn')->count(),
            'dang_phan_bien' => DeTai::whereIn('MaGV', $gvIds)->where('TrangThai', 'Đang phản biện đề cương')->count(),
            'da_phan_bien'   => DeTai::whereIn('MaGV', $gvIds)->where('TrangThai', 'Đã phản biện - Chờ duyệt BM')->count(),
            'cho_duyet_khoa' => DeTai::whereIn('MaGV', $gvIds)->where('TrangThai', 'Chờ duyệt cấp Khoa')->count(),
            'da_duyet_khoa'  => DeTai::whereIn('MaGV', $gvIds)->where('TrangThai', 'Trưởng khoa đã duyệt')->count(),
            'da_cong_bo'     => DeTai::whereIn('MaGV', $gvIds)->where('TrangThai', 'Đã công bố')->count(),
            'yeu_cau_sua'    => DeTai::whereIn('MaGV', $gvIds)->where('TrangThai', 'Yêu cầu chỉnh sửa')->count(),
            'tu_choi'        => DeTai::whereIn('MaGV', $gvIds)->where('TrangThai', 'Từ chối')->count(),
            'total'          => DeTai::whereIn('MaGV', $gvIds)->count(),
        ];

        return view('truongbomon.duyet_detai.index', compact('boMon', 'detais', 'counts', 'hocKies', 'giangViens', 'allGiangViens'));
    }

    public function show($id)
    {
        $boMon = $this->getBoMon();
        $detai = DeTai::with(['giangVien.boMon', 'hocKy', 'nganh', 'phanCongPhanBiens.giangVien'])
            ->findOrFail($id);

        $allGiangViens = GiangVien::where('MaGV', '!=', $detai->MaGV)->orderBy('HoTen')->get();
        $phanBienHienTai = $detai->phanCongPhanBiens->firstWhere('VaiTro', 'Phản biện đề cương');

        return view('truongbomon.duyet_detai.show', compact('boMon', 'detai', 'allGiangViens', 'phanBienHienTai'));
    }

    public function phanCongPhanBien(Request $request, $id)
    {
        $request->validate([
            'MaGVPhanBien' => 'required|exists:GiangVien,MaGV',
        ], [
            'MaGVPhanBien.required' => 'Vui lòng chọn Giảng viên phản biện đề cương.',
        ]);

        $detai = DeTai::findOrFail($id);

        // Quy tắc BR07: GV đề xuất đề tài không được làm GV phản biện chính đề tài đó
        if ($detai->MaGV === $request->MaGVPhanBien) {
            return redirect()->back()->withErrors('Giảng viên đề xuất đề tài không được làm Giảng viên phản biện cho đề tài này!');
        }

        DB::transaction(function () use ($request, $detai) {
            $maPC = IdGenerator::nextPhanCong();

            PhanCongPhanBien::updateOrCreate(
                [
                    'MaDeTai' => $detai->MaDeTai,
                    'VaiTro'  => 'Phản biện đề cương',
                ],
                [
                    'MaPhanCong'   => $maPC,
                    'MaGV'         => $request->MaGVPhanBien,
                    'NgayPhanCong' => now()->toDateString(),
                    'TrangThai'    => 'Đang phản biện',
                    'NhanXet'      => null,
                    'KetQua'       => null,
                    'NgayDanhGia'  => null,
                ]
            );

            $detai->update([
                'TrangThai' => 'Đang phản biện đề cương',
            ]);
        });

        // Gửi thông báo đến GV phản biện
        $gvPB = GiangVien::find($request->MaGVPhanBien);
        if ($gvPB && $gvPB->MaTK) {
            ThongBaoService::guiDen(
                $gvPB->MaTK,
                '📋 Phân công phản biện đề cương đề tài',
                "Bạn vừa được Trưởng bộ môn phân công phản biện đề cương cho đề tài: '{$detai->TenDeTai}'. Vui lòng xem và gửi nhận xét đánh giá.",
                'Đề tài'
            );
        }

        return redirect()->back()->with('success', "Đã phân công Giảng viên phản biện đề cương cho đề tài '{$detai->TenDeTai}' thành công!");
    }

    public function approve(Request $request, $id)
    {
        $user = Auth::user();
        $gv = GiangVien::where('MaTK', $user->MaTK)->first();

        $detai = DeTai::findOrFail($id);

        $detai->update([
            'TrangThai'    => 'Chờ duyệt cấp Khoa',
            'NgayDuyetBM'  => now(),
            'NguoiDuyetBM' => $gv ? $gv->MaGV : 'TBM',
            'LyDoTuChoi'   => null,
        ]);

        // Gửi thông báo cho GV đề xuất
        if ($detai->giangVien && $detai->giangVien->MaTK) {
            ThongBaoService::guiDen(
                $detai->giangVien->MaTK,
                '✅ Đề tài đã được Trưởng bộ môn phê duyệt',
                "Đề tài '{$detai->TenDeTai}' đã được Bộ môn phê duyệt và chuyển tiếp lên Trưởng khoa xem xét.",
                'Đề tài'
            );
        }

        return redirect()->back()->with('success', "Đã duyệt đề tài '{$detai->TenDeTai}' ở cấp Bộ môn thành công! Hồ sơ đã được chuyển tiếp lên Trưởng khoa.");
    }

    public function requestEdit(Request $request, $id)
    {
        $request->validate([
            'YeuCauSua' => 'required|string|max:1000',
        ], [
            'YeuCauSua.required' => 'Vui lòng nhập nội dung yêu cầu Giảng viên chỉnh sửa.',
        ]);

        $detai = DeTai::findOrFail($id);

        $detai->update([
            'TrangThai'   => 'Yêu cầu chỉnh sửa',
            'LyDoTuChoi'  => trim($request->YeuCauSua),
        ]);

        if ($detai->giangVien && $detai->giangVien->MaTK) {
            ThongBaoService::guiDen(
                $detai->giangVien->MaTK,
                '⚠️ Yêu cầu chỉnh sửa đề tài từ Trưởng bộ môn',
                "Đề tài '{$detai->TenDeTai}' có yêu cầu chỉnh sửa từ Trưởng bộ môn: " . trim($request->YeuCauSua),
                'Đề tài'
            );
        }

        return redirect()->back()->with('success', "Đã gửi yêu cầu chỉnh sửa đề tài tới Giảng viên đề xuất.");
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'LyDoTuChoi' => 'required|string|max:1000',
        ], [
            'LyDoTuChoi.required' => 'Vui lòng nhập lý do từ chối đề tài.',
        ]);

        $detai = DeTai::findOrFail($id);

        $detai->update([
            'TrangThai'  => 'Từ chối',
            'LyDoTuChoi' => trim($request->LyDoTuChoi),
        ]);

        if ($detai->giangVien && $detai->giangVien->MaTK) {
            ThongBaoService::guiDen(
                $detai->giangVien->MaTK,
                '❌ Đề tài bị Trưởng bộ môn từ chối',
                "Đề tài '{$detai->TenDeTai}' đã bị Trưởng bộ môn từ chối. Lý do: " . trim($request->LyDoTuChoi),
                'Đề tài'
            );
        }

        return redirect()->back()->with('success', "Đã từ chối đề tài '{$detai->TenDeTai}'.");
    }
}
