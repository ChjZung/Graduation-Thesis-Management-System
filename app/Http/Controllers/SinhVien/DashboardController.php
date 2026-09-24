<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\SinhVien;
use App\Models\ThanhVienNhom;
use App\Models\Nhom;
use App\Models\PhieuDangKy;
use App\Models\BaoCaoTienDo;
use App\Models\LichGapHuongDan;
use App\Models\HocKy;
use App\Models\MocThoiGianKhoaLuan;
use App\Models\KeHoachKhoaLuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $sinhVien = SinhVien::with(['khoa', 'nganh', 'lop'])->where('MaTK', $user->MaTK)->first();

        if (!$sinhVien) {
            return redirect()->route('login')->withErrors('Không tìm thấy thông tin sinh viên.');
        }

        $hocKyHienTai = HocKy::where('TrangThai', 'Đang mở')
            ->orWhere('TrangThai', 'active')
            ->first() ?? HocKy::latest('MaHocKy')->first();

        // Lấy thông tin nhóm & đề tài
        $thanhVienNhom = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)->first();
        $nhom = null;
        $phieuDangKy = null;
        $deTai = null;
        $gvhd = null;
        $truongNhom = null;
        $thanhViens = collect();
        $baoCaos = collect();
        $lichGaps = collect();

        if ($thanhVienNhom) {
            $nhom = Nhom::with([
                'thanhViens.sinhVien',
                'deTai.giangVien',
            ])->find($thanhVienNhom->MaNhom);

            if ($nhom) {
                $thanhViens = $nhom->thanhViens;
                $truongNhom = $thanhViens->firstWhere('VaiTro', 'Nhóm trưởng')?->sinhVien 
                    ?? $thanhViens->firstWhere('VaiTro', 'Trưởng nhóm')?->sinhVien
                    ?? $thanhViens->first()?->sinhVien;

                $phieuDangKy = PhieuDangKy::with('deTai.giangVien')
                    ->where('MaNhom', $nhom->MaNhom)
                    ->latest()
                    ->first();

                $deTai = $nhom->deTai ?? $phieuDangKy?->deTai;
                $gvhd = $deTai?->giangVien;

                $baoCaos = BaoCaoTienDo::with('tomTatBaoCao')
                    ->where('MaDeTai', $deTai?->MaDeTai ?? '')
                    ->orderBy('LanBaoCao')
                    ->get();

                $lichGaps = LichGapHuongDan::where('MaNhom', $nhom->MaNhom)
                    ->orderBy('ThoiGianBatDau')
                    ->take(2)
                    ->get();
            }
        }

        // Tính tiến độ tổng thể
        $soBaoCaoDat = $baoCaos->where('TrangThai', 'Đạt')->count();
        $tienDoPhanTram = $deTai ? max(75, min(100, round(($soBaoCaoDat / 5) * 100))) : 0;

        // Mốc tiếp theo cần nộp
        $mocTiepTheo = 2; // Mặc định hiển thị Mốc 2 theo Mockup
        foreach (range(1, 5) as $m) {
            $bc = $baoCaos->firstWhere('LanBaoCao', $m);
            if (!$bc || $bc->TrangThai !== 'Đạt') {
                $mocTiepTheo = $m;
                break;
            }
        }

        // Báo cáo gần nhất có tóm tắt AI
        $lastTomTat = null;
        foreach ($baoCaos as $bc) {
            if ($bc->tomTatBaoCao) {
                $lastTomTat = $bc->tomTatBaoCao;
            }
        }

        // Kiểm tra điều kiện làm khóa luận
        $isDuDieuKien = $sinhVien->isDuDieuKien();

        return view('sinhvien.dashboard', compact(
            'sinhVien',
            'isDuDieuKien',
            'hocKyHienTai',
            'nhom',
            'phieuDangKy',
            'deTai',
            'gvhd',
            'truongNhom',
            'thanhViens',
            'baoCaos',
            'tienDoPhanTram',
            'mocTiepTheo',
            'lichGaps',
            'lastTomTat'
        ));
    }
}
