<?php

namespace App\Http\Controllers\TruongBoMon;

use App\Http\Controllers\Controller;
use App\Models\BoMon;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\Nhom;
use App\Models\HocKy;
use App\Models\PhanCongPhanBien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private function getBoMon()
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if ($gv && $gv->MaBoMon) {
            $bm = BoMon::where('MaBoMon', $gv->MaBoMon)->first();
            if ($bm) return $bm;
        }

        if ($user && preg_match('/^TBM_(?:BM_)?([A-Z0-9]+)_/i', $user->TenDangNhap, $m)) {
            $bm = BoMon::where('MaBoMon', $m[1])->orWhere('MaBoMon', 'BM_' . $m[1])->first();
            if ($bm) return $bm;
        }

        return BoMon::where('TruongBoMon', 'like', '%' . ($gv->HoTen ?? '') . '%')->first() ?? BoMon::first();
    }

    public function index(Request $request)
    {
        $boMon = $this->getBoMon();
        $maBoMon = $boMon ? $boMon->MaBoMon : 'BM_CNPM';

        // Lấy danh sách GV thuộc Bộ môn
        $giangViens = GiangVien::where('MaBoMon', $maBoMon)->get();
        $gvIds = $giangViens->pluck('MaGV');

        $baseScope = function($q) use ($gvIds, $maBoMon) {
            $q->whereIn('MaGV', $gvIds)
              ->orWhereHas('hocPhanRef', fn($hp) => $hp->where('MaBoMon', $maBoMon));
        };

        // Thống kê Đề tài thuộc Bộ môn
        $totalDeTai = DeTai::where($baseScope)->count();
        $choDuyetBM = DeTai::where($baseScope)->where('TrangThai', 'Chờ duyệt cấp Bộ môn')->count();
        $dangPhanBien = DeTai::where($baseScope)->where('TrangThai', 'Đang phản biện đề cương')->count();
        $daPhanBien = DeTai::where($baseScope)->where('TrangThai', 'Đã phản biện - Chờ duyệt BM')->count();
        $choDuyetKhoa = DeTai::where($baseScope)->where('TrangThai', 'Chờ duyệt cấp Khoa')->count();
        $truongKhoaDaDuyet = DeTai::where($baseScope)->where('TrangThai', 'Trưởng khoa đã duyệt')->count();
        $daCongBo = DeTai::where($baseScope)->where('TrangThai', 'Đã công bố')->count();
        $yeuCauSua = DeTai::where($baseScope)->where('TrangThai', 'Yêu cầu chỉnh sửa')->count();
        $tuChoi = DeTai::where($baseScope)->where('TrangThai', 'Từ chối')->count();

        // Đề tài chờ phân công phản biện đề cương (sau khi đề cương đã nộp và chưa có GV phản biện)
        $choPhanCongPB = DeTai::where($baseScope)
            ->whereIn('TrangThai', ['Đã nộp đề cương - Chờ phân công PB', 'Trưởng khoa đã duyệt - Chờ nộp đề cương'])
            ->whereDoesntHave('phanCongPhanBiens', fn($q) => $q->where('VaiTro', 'Phản biện đề cương'))
            ->count();

        // Danh sách đề tài cần xử lý ngay tại Bộ môn
        $deTaiCanXuLy = DeTai::with(['giangVien', 'hocKy', 'phanCongPhanBiens.giangVien'])
            ->where($baseScope)
            ->whereIn('TrangThai', [
                'Chờ duyệt cấp Bộ môn',
                'Đã nộp đề cương - Chờ phân công PB',
                'Đã phản biện - Chờ duyệt BM',
                'Đang phản biện đề cương'
            ])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        // Thống kê nhóm sinh viên thực hiện đề tài của Bộ môn
        $nhomBoMon = Nhom::whereHas('deTai', fn($q) => $q->whereIn('MaGV', $gvIds))->count();

        $stats = [
            'total_de_tai'        => $totalDeTai,
            'total_detai'         => $totalDeTai,
            'cho_duyet_bm'        => $choDuyetBM,
            'cho_phan_cong_pb'    => $choPhanCongPB,
            'dang_phan_bien'      => $dangPhanBien,
            'da_phan_bien'        => $daPhanBien,
            'cho_duyet_khoa'      => $choDuyetKhoa,
            'truong_khoa_da_duyet'=> $truongKhoaDaDuyet,
            'da_cong_bo'          => $daCongBo,
            'yeu_cau_sua'         => $yeuCauSua,
            'tu_choi'             => $tuChoi,
            'total_gv'            => $giangViens->count(),
            'total_nhom'          => $nhomBoMon,
        ];

        // Thống kê phân bổ đề tài theo Giảng viên trong Bộ môn (Top 8 GV)
        $gvStats = GiangVien::where('MaBoMon', $maBoMon)
            ->withCount('deTais as so_de_tai')
            ->orderByDesc('so_de_tai')
            ->take(8)
            ->get();

        $chartGvLabels = $gvStats->pluck('HoTen')->toArray();
        $chartGvCounts = $gvStats->pluck('so_de_tai')->toArray();

        // Thống kê trạng thái đề tài cho biểu đồ
        $chartStatusCounts = [
            'cho_duyet_bm'     => $choDuyetBM,
            'dang_phan_bien'   => $dangPhanBien + $daPhanBien,
            'cho_duyet_khoa'   => $choDuyetKhoa,
            'da_duyet_cong_bo' => $truongKhoaDaDuyet + $daCongBo,
            'can_sua_tu_choi'  => $yeuCauSua + $tuChoi,
        ];

        return view('truongbomon.dashboard', compact(
            'boMon', 
            'stats', 
            'deTaiCanXuLy', 
            'giangViens',
            'chartGvLabels',
            'chartGvCounts',
            'chartStatusCounts'
        ));
    }
}
