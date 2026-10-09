<?php

namespace App\Http\Controllers\TruongKhoa;

use App\Http\Controllers\Controller;
use App\Models\Khoa;
use App\Models\BoMon;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\SinhVien;
use App\Models\Nhom;
use App\Models\HoiDong;
use App\Models\KetQuaSinhVien;
use App\Models\HocKy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private function getKhoa()
    {
        $user = Auth::user();
        if ($user && preg_match('/^TK_([A-Z0-9]+)_/i', $user->TenDangNhap, $m)) {
            $khoa = Khoa::where('MaKhoa', $m[1])->first();
            if ($khoa) return $khoa;
        }

        $gv = GiangVien::getLoggedInGiangVien($user);
        if ($gv) {
            $gv->loadMissing('boMon.khoa');
            if ($gv->boMon && $gv->boMon->khoa) {
                return $gv->boMon->khoa;
            }
        }

        return Khoa::where('MaKhoa', 'CNTT')->first() ?? Khoa::first();
    }

    public function index(Request $request)
    {
        $khoa = $this->getKhoa();
        $maKhoa = $khoa ? $khoa->MaKhoa : 'CNTT';

        // Thống kê toàn Khoa
        $soBoMon     = BoMon::where('MaKhoa', $maKhoa)->count();
        $soGiangVien = GiangVien::whereHas('boMon', fn($q) => $q->where('MaKhoa', $maKhoa))->count();
        $soSinhVien  = SinhVien::where('MaKhoa', $maKhoa)->count();
        $soDeTai     = DeTai::whereHas('giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa))->count();
        $soNhom      = Nhom::whereHas('sinhViens', fn($q) => $q->where('MaKhoa', $maKhoa))->count();
        $soHoiDong   = HoiDong::count();

        // Đề tài cần Trưởng khoa duyệt (Phân quyền cấp Khoa)
        $baseDeTai = DeTai::whereHas('giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa));
        $choDuyetKhoa      = (clone $baseDeTai)->where('TrangThai', 'Chờ duyệt cấp Khoa')->count();
        $truongKhoaDaDuyet = (clone $baseDeTai)->where('TrangThai', 'Trưởng khoa đã duyệt')->count();
        $daCongBo          = (clone $baseDeTai)->where('TrangThai', 'Đã công bố')->count();
        $choDuyetBM        = (clone $baseDeTai)->where('TrangThai', 'Chờ duyệt cấp Bộ môn')->count();
        $dangPhanBien      = (clone $baseDeTai)->where('TrangThai', 'Đang phản biện đề cương')->count();
        $yeuCauSua         = (clone $baseDeTai)->where('TrangThai', 'Yêu cầu chỉnh sửa')->count();
        $tuChoi            = (clone $baseDeTai)->where('TrangThai', 'Từ chối')->count();

        // Danh sách đề tài đang chờ Trưởng khoa duyệt
        $deTaiCanDuyet = DeTai::with(['giangVien.boMon', 'hocKy', 'phanCongPhanBiens.giangVien'])
            ->whereHas('giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa))
            ->where('TrangThai', 'Chờ duyệt cấp Khoa')
            ->orderBy('NgayDuyetBM', 'desc')
            ->limit(6)
            ->get();

        // Xếp loại toàn khoa
        $ketQuaXepLoai = KetQuaSinhVien::whereHas('sinhVien', fn($q) => $q->where('MaKhoa', $maKhoa))
            ->selectRaw('KetQua, count(*) as total')
            ->whereNotNull('KetQua')
            ->groupBy('KetQua')
            ->pluck('total', 'KetQua')
            ->toArray();

        $stats = [
            'so_bo_mon'           => $soBoMon,
            'so_giang_vien'       => $soGiangVien,
            'so_sinh_vien'        => $soSinhVien,
            'so_de_tai'           => $soDeTai,
            'so_nhom'             => $soNhom,
            'so_hoi_dong'         => $soHoiDong,
            'cho_duyet_khoa'      => $choDuyetKhoa,
            'truong_khoa_da_duyet'=> $truongKhoaDaDuyet,
            'da_cong_bo'          => $daCongBo,
            'cho_duyet_bm'        => $choDuyetBM,
            'dang_phan_bien'      => $dangPhanBien,
            'yeu_cau_sua'         => $yeuCauSua,
            'tu_choi'             => $tuChoi,
        ];

        // Thống kê phân bổ đề tài theo Bộ môn
        $boMonList = BoMon::where('MaKhoa', $maKhoa)->get();
        $chartBmLabels = [];
        $chartBmCounts = [];
        foreach ($boMonList as $bm) {
            $chartBmLabels[] = $bm->TenBoMon;
            $chartBmCounts[] = DeTai::whereHas('giangVien', fn($q) => $q->where('MaBoMon', $bm->MaBoMon))->count();
        }

        // Thống kê trạng thái đề tài toàn Khoa
        $chartStatusKhoa = [
            'cho_duyet_khoa'    => $choDuyetKhoa,
            'truong_khoa_duyet' => $truongKhoaDaDuyet,
            'da_cong_bo'        => $daCongBo,
            'dang_xu_ly_bm'     => $choDuyetBM + $dangPhanBien,
            'can_sua_tu_choi'   => $yeuCauSua + $tuChoi,
        ];

        return view('truongkhoa.dashboard', compact(
            'khoa', 
            'stats', 
            'deTaiCanDuyet', 
            'ketQuaXepLoai',
            'chartBmLabels',
            'chartBmCounts',
            'chartStatusKhoa'
        ));
    }
}
