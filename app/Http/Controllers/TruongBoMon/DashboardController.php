<?php

namespace App\Http\Controllers\TruongBoMon;

use App\Http\Controllers\Controller;
use App\Models\BoMon;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\HocKy;
use App\Models\PhanCongPhanBien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private function getBoMon(): BoMon
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

        abort(403, 'Không thể xác định Bộ môn của Trưởng bộ môn này. Vui lòng liên hệ Giáo vụ.');
    }

    public function index(Request $request)
    {
        $boMon = $this->getBoMon();
        $maBoMon = $boMon->MaBoMon;

        // Lấy danh sách GV thuộc Bộ môn
        $giangViens = GiangVien::where('MaBoMon', $maBoMon)->get();
        $gvIds = $giangViens->pluck('MaGV');

        // Thống kê Đề tài thuộc Bộ môn (bao gồm trạng thái "Không đạt phản biện")
        $totalDeTai       = DeTai::whereIn('MaGV', $gvIds)->count();
        $choDuyetBM       = DeTai::whereIn('MaGV', $gvIds)->where('TrangThai', 'Chờ duyệt cấp Bộ môn')->count();
        $dangPhanBien     = DeTai::whereIn('MaGV', $gvIds)->where('TrangThai', 'Đang phản biện đề cương')->count();
        $daPhanBien       = DeTai::whereIn('MaGV', $gvIds)->where('TrangThai', 'Đã phản biện - Chờ duyệt BM')->count();
        $choDuyetKhoa     = DeTai::whereIn('MaGV', $gvIds)->where('TrangThai', 'Chờ duyệt cấp Khoa')->count();
        $truongKhoaDaDuyet= DeTai::whereIn('MaGV', $gvIds)->where('TrangThai', 'Trưởng khoa đã duyệt')->count();
        $daCongBo         = DeTai::whereIn('MaGV', $gvIds)->where('TrangThai', 'Đã công bố')->count();
        $yeuCauSua        = DeTai::whereIn('MaGV', $gvIds)->where('TrangThai', 'Yêu cầu chỉnh sửa')->count();
        $tuChoi           = DeTai::whereIn('MaGV', $gvIds)->where('TrangThai', 'Từ chối')->count();
        $khongDat         = DeTai::whereIn('MaGV', $gvIds)->where('TrangThai', 'Không đạt phản biện')->count();

        // Kết quả phản biện đề cương theo Bộ môn
        $pbDat     = PhanCongPhanBien::whereIn('MaDeTai', DeTai::whereIn('MaGV', $gvIds)->pluck('MaDeTai'))
            ->where('VaiTro', 'Phản biện đề cương')
            ->where('KetQua', 'Đạt')->count();
        $pbSua     = PhanCongPhanBien::whereIn('MaDeTai', DeTai::whereIn('MaGV', $gvIds)->pluck('MaDeTai'))
            ->where('VaiTro', 'Phản biện đề cương')
            ->where('KetQua', 'Yêu cầu chỉnh sửa')->count();
        $pbKhongDat= PhanCongPhanBien::whereIn('MaDeTai', DeTai::whereIn('MaGV', $gvIds)->pluck('MaDeTai'))
            ->where('VaiTro', 'Phản biện đề cương')
            ->where('KetQua', 'Không đạt')->count();

        // Danh sách đề tài cần xử lý ngay tại Bộ môn
        $deTaiCanXuLy = DeTai::with(['giangVien', 'hocKy', 'phanCongPhanBiens.giangVien'])
            ->whereIn('MaGV', $gvIds)
            ->whereIn('TrangThai', [
                'Chờ duyệt cấp Bộ môn',
                'Đã phản biện - Chờ duyệt BM',
                'Đang phản biện đề cương',
                'Không đạt phản biện',
            ])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        // Thống kê nhóm sinh viên thực hiện đề tài của Bộ môn
        $nhomBoMon = \App\Models\Nhom::whereHas('deTai', fn($q) => $q->whereIn('MaGV', $gvIds))->count();

        $stats = [
            'total_de_tai'         => $totalDeTai,
            'cho_duyet_bm'         => $choDuyetBM,
            'dang_phan_bien'       => $dangPhanBien,
            'da_phan_bien'         => $daPhanBien,
            'cho_duyet_khoa'       => $choDuyetKhoa,
            'truong_khoa_da_duyet' => $truongKhoaDaDuyet,
            'da_cong_bo'           => $daCongBo,
            'yeu_cau_sua'          => $yeuCauSua,
            'tu_choi'              => $tuChoi,
            'khong_dat'            => $khongDat,
            'total_gv'             => $giangViens->count(),
            'total_nhom'           => $nhomBoMon,
            'pb_dat'               => $pbDat,
            'pb_sua'               => $pbSua,
            'pb_khong_dat'         => $pbKhongDat,
        ];

        return view('truongbomon.dashboard', compact('boMon', 'stats', 'deTaiCanXuLy', 'giangViens'));
    }
}
