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
        $maKhoa = $khoa ? $khoa->MaKhoa : 'CNTT';

        // Thống kê toàn Khoa
        $soBoMon     = BoMon::where('MaKhoa', $maKhoa)->count();
        $soGiangVien = GiangVien::whereHas('boMon', fn($q) => $q->where('MaKhoa', $maKhoa))->count();
        $soSinhVien  = SinhVien::where('MaKhoa', $maKhoa)->count();
        $soDeTai     = DeTai::count();
        $soNhom      = Nhom::count();
        $soHoiDong   = HoiDong::count();

        // Đề tài cần Trưởng khoa duyệt
        $choDuyetKhoa = DeTai::where('TrangThai', 'Chờ duyệt cấp Khoa')->count();
        $truongKhoaDaDuyet = DeTai::where('TrangThai', 'Trưởng khoa đã duyệt')->count();
        $daCongBo = DeTai::where('TrangThai', 'Đã công bố')->count();
        $choDuyetBM = DeTai::where('TrangThai', 'Chờ duyệt cấp Bộ môn')->count();
        $dangPhanBien = DeTai::where('TrangThai', 'Đang phản biện đề cương')->count();

        // Danh sách đề tài đang chờ Trưởng khoa duyệt
        $deTaiCanDuyet = DeTai::with(['giangVien.boMon', 'hocKy', 'phanCongPhanBiens.giangVien'])
            ->where('TrangThai', 'Chờ duyệt cấp Khoa')
            ->orderBy('NgayDuyetBM', 'desc')
            ->limit(6)
            ->get();

        // Xếp loại toàn khoa
        $ketQuaXepLoai = KetQuaSinhVien::selectRaw('KetQua, count(*) as total')
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
        ];

        return view('truongkhoa.dashboard', compact('khoa', 'stats', 'deTaiCanDuyet', 'ketQuaXepLoai'));
    }
}
