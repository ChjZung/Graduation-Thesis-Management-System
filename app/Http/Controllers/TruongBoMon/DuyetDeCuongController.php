<?php

namespace App\Http\Controllers\TruongBoMon;

use App\Http\Controllers\Controller;
use App\Models\BoMon;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\HocKy;
use App\Models\PhanCongPhanBien;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DuyetDeCuongController extends Controller
{
    private function getBoMon()
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if ($gv && $gv->MaBoMon) {
            $bm = BoMon::where('MaBoMon', $gv->MaBoMon)->first();
            if ($bm) return $bm;
        }

        if ($user && preg_match('/^TBM_[A-Z0-9]+_([A-Z0-9]+)_/i', $user->TenDangNhap, $m)) {
            $bm = BoMon::where('MaBoMon', $m[1])->first();
            if ($bm) return $bm;
        }

        return BoMon::where('TruongBoMon', 'like', '%' . ($gv->HoTen ?? '') . '%')->first() ?? BoMon::first();
    }

    public function index(Request $request)
    {
        $boMon = $this->getBoMon();
        $maBoMon = $boMon ? $boMon->MaBoMon : 'CNPM';

        $giangViens = GiangVien::where('MaBoMon', $maBoMon)->orderBy('HoTen')->get();
        $gvIds = $giangViens->pluck('MaGV');

        // Danh sách học kỳ
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();
        $currentHocKy = HocKy::where('TrangThai', 'Đang diễn ra')->first() ?? $hocKies->first();
        $selectedHocKy = $request->input('MaHocKy', $currentHocKy?->MaHocKy ?? '');

        // Base query các đề tài có file đề cương hoặc bước phản biện / duyệt đề cương
        $baseQuery = DeTai::with(['giangVien.boMon', 'nganh', 'hocKy', 'phanCongPhanBiens.giangVien'])
            ->whereIn('MaGV', $gvIds)
            ->where(function($q) {
                $q->whereNotNull('FileDeCuong')
                  ->orWhereIn('TrangThai', [
                      'Đã nộp đề cương - Chờ phân công PB',
                      'Đang phản biện đề cương',
                      'Đã cập nhật đề cương - Chờ phản biện lại',
                      'Đã phản biện - Chờ duyệt BM',
                      'Đã phản biện - Chờ TBM duyệt đề cương',
                      'Đã công bố',
                      'Yêu cầu chỉnh sửa đề cương',
                      'Không đạt phản biện'
                  ]);
            });

        if ($selectedHocKy && $selectedHocKy !== 'ALL') {
            $baseQuery->where('MaHocKy', $selectedHocKy);
        }

        // Lọc theo Giảng viên đề xuất
        if ($request->filled('MaGV')) {
            $baseQuery->where('MaGV', $request->MaGV);
        }

        // Tìm kiếm
        if ($request->filled('search')) {
            $s = trim($request->search);
            $baseQuery->where(function ($q) use ($s) {
                $q->where('TenDeTai', 'like', "%{$s}%")
                  ->orWhere('MaDeTai', 'like', "%{$s}%")
                  ->orWhereHas('giangVien', fn($g) => $g->where('HoTen', 'like', "%{$s}%"));
            });
        }

        // Tab lọc
        $statusTab = $request->input('tab', 'cho_duyet');
        $query = clone $baseQuery;

        if ($statusTab === 'cho_duyet') {
            // Đề tài đã phản biện xong (Đạt) đang chờ TBM duyệt công bố
            $query->where(function($q) {
                $q->whereIn('TrangThai', ['Đã phản biện - Chờ duyệt BM', 'Đã phản biện - Chờ TBM duyệt đề cương'])
                  ->orWhereHas('phanCongPhanBiens', fn($pq) => $pq->where('VaiTro', 'Phản biện đề cương')->where('KetQua', 'Đạt'));
            })->where('TrangThai', '!=', 'Đã công bố');
        } elseif ($statusTab === 'dang_phan_bien') {
            $query->whereIn('TrangThai', ['Đang phản biện đề cương', 'Đã cập nhật đề cương - Chờ phản biện lại']);
        } elseif ($statusTab === 'yeu_cau_sua') {
            $query->whereIn('TrangThai', ['Yêu cầu chỉnh sửa đề cương']);
        } elseif ($statusTab === 'da_cong_bo') {
            $query->where('TrangThai', 'Đã công bố');
        } elseif ($statusTab === 'ALL') {
            // Hiển thị tất cả đề tài trong giai đoạn đề cương
        }

        $detais = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // Counts
        $counts = [
            'cho_duyet' => (clone $baseQuery)->where(function($q) {
                $q->whereIn('TrangThai', ['Đã phản biện - Chờ duyệt BM', 'Đã phản biện - Chờ TBM duyệt đề cương'])
                  ->orWhereHas('phanCongPhanBiens', fn($pq) => $pq->where('VaiTro', 'Phản biện đề cương')->where('KetQua', 'Đạt'));
            })->where('TrangThai', '!=', 'Đã công bố')->count(),

            'dang_phan_bien' => (clone $baseQuery)->whereIn('TrangThai', ['Đang phản biện đề cương', 'Đã cập nhật đề cương - Chờ phản biện lại'])->count(),

            'yeu_cau_sua' => (clone $baseQuery)->whereIn('TrangThai', ['Yêu cầu chỉnh sửa đề cương'])->count(),

            'da_cong_bo' => (clone $baseQuery)->where('TrangThai', 'Đã công bố')->count(),

            'total' => (clone $baseQuery)->count(),
        ];

        return view('truongbomon.duyet_decuong.index', compact('boMon', 'detais', 'counts', 'hocKies', 'giangViens', 'selectedHocKy', 'statusTab'));
    }

    public function duyetVaCongBo(Request $request, $id)
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        $maGVTBM = $gv ? $gv->MaGV : ($user->TenDangNhap ?? 'TBM');

        $detai = DeTai::findOrFail($id);

        $phanBien = $detai->phanCongPhanBiens->firstWhere('VaiTro', 'Phản biện đề cương');
        if (!$phanBien || $phanBien->KetQua !== 'Đạt') {
            return redirect()->back()->withErrors('Đề cương chưa có kết quả phản biện ĐẠT. Không thể phê duyệt công bố đề tài.');
        }

        DB::transaction(function () use ($detai, $maGVTBM) {
            $detai->update([
                'TrangThai'    => 'Đã công bố',
                'NgayCongBo'   => now(),
                'NgayDuyetBM'  => now(),
                'NguoiDuyetBM' => $maGVTBM,
            ]);
        });

        // Gửi thông báo đến GV đề xuất
        if ($detai->giangVien && $detai->giangVien->MaTK) {
            ThongBaoService::guiDen(
                $detai->giangVien->MaTK,
                '🎉 Đề tài đã được Trưởng bộ môn duyệt đề cương và CÔNG BỐ',
                "Đề tài '{$detai->TenDeTai}' đã hoàn tất phản biện đề cương đạt yêu cầu và được Trưởng bộ môn phê duyệt chính thức CÔNG BỐ cho sinh viên đăng ký!",
                'Đề tài'
            );
        }

        return redirect()->back()->with('success', "Đã phê duyệt đề cương và CÔNG BỐ đề tài '{$detai->TenDeTai}' thành công! Sinh viên hiện đã có thể xem và đăng ký đề tài này.");
    }

    public function requestEdit(Request $request, $id)
    {
        $request->validate([
            'YeuCauSua' => 'required|string|max:1000',
        ], [
            'YeuCauSua.required' => 'Vui lòng nhập nội dung yêu cầu Giảng viên chỉnh sửa đề cương.',
        ]);

        $detai = DeTai::findOrFail($id);

        $detai->update([
            'TrangThai'  => 'Yêu cầu chỉnh sửa đề cương',
            'LyDoTuChoi' => trim($request->YeuCauSua),
        ]);

        if ($detai->giangVien && $detai->giangVien->MaTK) {
            ThongBaoService::guiDen(
                $detai->giangVien->MaTK,
                '⚠️ Yêu cầu chỉnh sửa đề cương từ Trưởng bộ môn',
                "Trưởng bộ môn đã xem xét đề cương đề tài '{$detai->TenDeTai}' và yêu cầu chỉnh sửa: '{$request->YeuCauSua}'. Vui lòng nộp lại file đề cương hoàn thiện.",
                'Đề tài'
            );
        }

        return redirect()->back()->with('success', "Đã gửi yêu cầu chỉnh sửa đề cương đề tài '{$detai->TenDeTai}' tới Giảng viên hướng dẫn thành công!");
    }
}
