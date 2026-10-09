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
use App\Models\HoSoBaoVe;
use App\Models\KetQuaSinhVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $sinhVien = SinhVien::with(['khoa', 'nganh', 'lop.nganh.khoa'])->where('MaTK', $user->MaTK)->first();

        if (!$sinhVien) {
            return redirect()->route('login')->withErrors('Không tìm thấy thông tin sinh viên.');
        }

        $hocKyHienTai = HocKy::where('TrangThai', 'Đang diễn ra')
            ->orWhere('TrangThai', '1')
            ->orWhere('TrangThai', 'Đang mở')
            ->orderBy('MaHocKy', 'desc')
            ->first() ?? HocKy::latest('MaHocKy')->first();

        // 1. Kiểm tra điều kiện xét duyệt làm khóa luận
        $dkRecord = null;
        if ($hocKyHienTai) {
            $dkRecord = \App\Models\DanhSachSVDuDieuKien::where('MaSV', $sinhVien->MaSV)
                ->where('MaHocKy', $hocKyHienTai->MaHocKy)
                ->first();
        }
        $isDuDieuKien = $dkRecord ? ($dkRecord->TrangThai === 'Đủ điều kiện') : $sinhVien->isDuDieuKien();

        // 2. Lấy thông tin nhóm & đề tài
        $thanhVienNhom = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'da_tham_gia')
            ->first() ?? ThanhVienNhom::where('MaSV', $sinhVien->MaSV)->first();

        $nhom = null;
        $phieuDangKy = null;
        $deTai = null;
        $gvhd = null;
        $truongNhom = null;
        $thanhViens = collect();
        $baoCaos = collect();
        $lichGaps = collect();
        $hoSoBaoVe = null;

        if ($thanhVienNhom) {
            $nhom = Nhom::with([
                'thanhViens.sinhVien.lop',
                'deTai.giangVien.boMon',
                'hocPhan',
                'hocKy',
            ])->find($thanhVienNhom->MaNhom);

            if ($nhom) {
                $thanhViens = $nhom->thanhViens;
                $truongNhom = $thanhViens->firstWhere('VaiTro', 'Nhóm trưởng')?->sinhVien 
                    ?? $thanhViens->firstWhere('VaiTro', 'Trưởng nhóm')?->sinhVien
                    ?? $thanhViens->first()?->sinhVien;

                $phieuDangKy = PhieuDangKy::with('deTai.giangVien.boMon')
                    ->where('MaNhom', $nhom->MaNhom)
                    ->latest()
                    ->first();

                $deTai = $nhom->deTai ?? $phieuDangKy?->deTai;
                $gvhd = $deTai?->giangVien;

                if ($deTai) {
                    $baoCaos = BaoCaoTienDo::with('tomTatBaoCao')
                        ->where('MaDeTai', $deTai->MaDeTai)
                        ->orderBy('LanBaoCao')
                        ->get();
                }

                $lichGaps = LichGapHuongDan::where('MaNhom', $nhom->MaNhom)
                    ->orderBy('ThoiGianBatDau', 'desc')
                    ->take(3)
                    ->get();

                $hoSoBaoVe = HoSoBaoVe::with(['hoiDong.thanhViens.giangVien'])
                    ->where('MaNhom', $nhom->MaNhom)
                    ->first();
            }
        }

        // 3. Lấy Kế hoạch khóa luận & Mốc thời gian
        $activePlan = KeHoachKhoaLuan::with(['mocThoiGians' => fn($q) => $q->orderBy('NgayBatDau')])
            ->where(function($q) use ($hocKyHienTai) {
                if ($hocKyHienTai) {
                    $q->where('MaHocKy', $hocKyHienTai->MaHocKy);
                }
            })
            ->whereIn('TrangThai', ['ĐANG THỰC HIỆN', 'ĐÃ CÔNG BỐ', '1'])
            ->first() ?? KeHoachKhoaLuan::with(['mocThoiGians' => fn($q) => $q->orderBy('NgayBatDau')])->latest()->first();

        $allMocs = $activePlan ? $activePlan->mocThoiGians : collect();
        $today = now()->format('Y-m-d');
        
        $nextMoc = $allMocs->first(function($m) use ($today) {
            return $m->NgayKetThuc >= $today;
        }) ?? $allMocs->last();

        $daysRemaining = null;
        if ($nextMoc && $nextMoc->NgayKetThuc) {
            $endCarbon = Carbon::parse($nextMoc->NgayKetThuc)->endOfDay();
            $daysRemaining = (int) ceil(now()->diffInDays($endCarbon, false));
        }

        // 4. Kết quả khóa luận (nếu có)
        $ketQua = KetQuaSinhVien::where('MaSV', $sinhVien->MaSV)->first();

        // 5. Tính toán tiến độ chuẩn xác theo chu trình thực hiện
        $soBaoCaoDat = $baoCaos->where('TrangThai', 'Đạt')->count();
        if ($ketQua && $ketQua->DiemTongKet > 0) {
            $tienDoPhanTram = 100;
        } elseif ($hoSoBaoVe && in_array($hoSoBaoVe->TrangThai, ['Đủ điều kiện bảo vệ', 'Đã phân công', 'Đã bảo vệ'])) {
            $tienDoPhanTram = 90;
        } elseif ($hoSoBaoVe) {
            $tienDoPhanTram = 80;
        } elseif ($deTai) {
            $tienDoPhanTram = min(75, 20 + ($soBaoCaoDat * 11));
        } elseif ($nhom) {
            $tienDoPhanTram = 15;
        } elseif ($isDuDieuKien) {
            $tienDoPhanTram = 10;
        } else {
            $tienDoPhanTram = 0;
        }

        // 6. Mốc báo cáo tiến độ tiếp theo cần nộp
        $mocTiepTheo = 1;
        foreach (range(1, 5) as $m) {
            $bc = $baoCaos->firstWhere('LanBaoCao', $m);
            if (!$bc || $bc->TrangThai !== 'Đạt') {
                $mocTiepTheo = $m;
                break;
            }
            $mocTiepTheo = 5;
        }

        // 7. Nhận xét / Phản hồi mới nhất từ Giảng viên hướng dẫn
        $latestFeedback = $baoCaos->whereNotNull('NhanXet')->sortByDesc('updated_at')->first()
            ?? $baoCaos->sortByDesc('LanBaoCao')->first();

        return view('sinhvien.dashboard', compact(
            'sinhVien',
            'isDuDieuKien',
            'dkRecord',
            'hocKyHienTai',
            'nhom',
            'phieuDangKy',
            'deTai',
            'gvhd',
            'truongNhom',
            'thanhViens',
            'baoCaos',
            'soBaoCaoDat',
            'tienDoPhanTram',
            'mocTiepTheo',
            'lichGaps',
            'hoSoBaoVe',
            'ketQua',
            'activePlan',
            'nextMoc',
            'daysRemaining',
            'latestFeedback'
        ));
    }
}
