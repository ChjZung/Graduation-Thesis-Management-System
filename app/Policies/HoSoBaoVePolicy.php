<?php

namespace App\Policies;

use App\Models\GiangVien;
use App\Models\HoSoBaoVe;
use App\Models\TaiKhoan;
use App\Models\SinhVien;
use App\Models\ThanhVienNhom;
use App\Models\ThanhVienHoiDong;

class HoSoBaoVePolicy
{
    /**
     * Xác định ai có quyền xem hồ sơ bảo vệ (và các file Turnitin, khóa luận đính kèm).
     */
    public function view(TaiKhoan $user, HoSoBaoVe $hoSo): bool
    {
        $user->loadMissing('vaiTro');
        $role = $user->vaiTro->TenVaiTro ?? '';

        if (in_array($role, ['Admin', 'Giáo vụ'])) {
            return true;
        }

        $hoSo->loadMissing('nhom.thanhViens', 'deTai.giangVien.boMon');

        // Sinh viên thuộc nhóm này
        if ($role === 'Sinh viên') {
            $sv = SinhVien::where('MaTK', $user->MaTK)->first();
            if (!$sv) return false;

            return ThanhVienNhom::where('MaSV', $sv->MaSV)
                ->where('MaNhom', $hoSo->MaNhom)
                ->where('TrangThai', 'da_tham_gia')
                ->exists();
        }

        // Giảng viên
        if (in_array($role, ['Giảng viên', 'Trưởng bộ môn', 'Trưởng khoa'])) {
            $gv = GiangVien::getLoggedInGiangVien($user);
            if (!$gv) return false;

            // 1. Giảng viên hướng dẫn
            if ($hoSo->deTai && $hoSo->deTai->MaGV === $gv->MaGV) {
                return true;
            }

            // 2. Giảng viên thuộc Hội đồng bảo vệ của nhóm này
            if ($hoSo->MaHoiDong && ThanhVienHoiDong::where('MaHoiDong', $hoSo->MaHoiDong)->where('MaGV', $gv->MaGV)->exists()) {
                return true;
            }

            // 3. Trưởng bộ môn phụ trách bộ môn của đề tài
            if ($role === 'Trưởng bộ môn') {
                $maBoMon = $gv->MaBoMon;
                if (!$maBoMon && preg_match('/^TBM_(?:BM_)?([A-Z0-9]+)_/i', $user->TenDangNhap, $m)) {
                    $maBoMon = $m[1];
                }
                $dtBM = $hoSo->deTai?->giangVien?->MaBoMon;
                if ($maBoMon && $dtBM && ($maBoMon === $dtBM || 'BM_' . $maBoMon === $dtBM || $maBoMon === 'BM_' . $dtBM)) {
                    return true;
                }
            }

            // 4. Trưởng khoa phụ trách khoa của đề tài
            if ($role === 'Trưởng khoa') {
                $maKhoa = $gv->boMon?->MaKhoa;
                if (!$maKhoa && preg_match('/^TK_([A-Z0-9]+)_/i', $user->TenDangNhap, $m)) {
                    $maKhoa = $m[1];
                }
                $dtKhoa = $hoSo->deTai?->giangVien?->boMon?->MaKhoa;
                if ($maKhoa && $dtKhoa && $maKhoa === $dtKhoa) {
                    return true;
                }
            }

            // 5. Giảng viên được phân công kiểm tra báo cáo sau bảo vệ
            if ($hoSo->NguoiKiemTraChinhSua === $gv->MaGV 
                || ($hoSo->NguoiKiemTraChinhSua && str_contains($hoSo->NguoiKiemTraChinhSua, $gv->HoTen))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Giảng viên chỉ được chấm điểm nếu thuộc danh sách Hội đồng bảo vệ của hồ sơ đó.
     */
    public function grade(TaiKhoan $user, HoSoBaoVe $hoSo): bool
    {
        $gv = GiangVien::getLoggedInGiangVien($user);
        if (!$gv || !$hoSo->MaHoiDong) return false;

        return ThanhVienHoiDong::where('MaHoiDong', $hoSo->MaHoiDong)
            ->where('MaGV', $gv->MaGV)
            ->exists();
    }

    /**
     * Chỉ GVHD mới được xác nhận đủ điều kiện bảo vệ.
     */
    public function confirm(TaiKhoan $user, HoSoBaoVe $hoSo): bool
    {
        $gv = GiangVien::getLoggedInGiangVien($user);
        if (!$gv) return false;

        $hoSo->loadMissing('deTai');
        return $hoSo->deTai && $hoSo->deTai->MaGV === $gv->MaGV;
    }
}
