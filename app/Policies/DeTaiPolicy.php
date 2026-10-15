<?php

namespace App\Policies;

use App\Models\GiangVien;
use App\Models\DeTai;
use App\Models\TaiKhoan;
use App\Models\SinhVien;
use App\Models\ThanhVienNhom;

class DeTaiPolicy
{
    /**
     * Xác định người dùng có thể xem đề tài không.
     */
    public function view(TaiKhoan $user, DeTai $deTai): bool
    {
        $user->loadMissing('vaiTro');
        $role = $user->vaiTro->TenVaiTro ?? '';

        // Giáo vụ / Admin được xem mọi đề tài
        if (in_array($role, ['Admin', 'Giáo vụ'])) {
            return true;
        }

        // Trưởng khoa: được xem đề tài thuộc khoa mình phụ trách
        if ($role === 'Trưởng khoa') {
            $gv = GiangVien::getLoggedInGiangVien($user);
            $maKhoa = $gv?->boMon?->MaKhoa;
            if (!$maKhoa && preg_match('/^TK_([A-Z0-9]+)_/i', $user->TenDangNhap, $m)) {
                $maKhoa = $m[1];
            }
            $detaiKhoa = $deTai->giangVien?->boMon?->MaKhoa ?? $deTai->hocPhanRef?->boMon?->MaKhoa;
            return $maKhoa && $detaiKhoa && $maKhoa === $detaiKhoa;
        }

        // Trưởng bộ môn: được xem đề tài thuộc bộ môn mình phụ trách
        if ($role === 'Trưởng bộ môn') {
            $gv = GiangVien::getLoggedInGiangVien($user);
            $maBoMon = $gv?->MaBoMon;
            if (!$maBoMon && preg_match('/^TBM_(?:BM_)?([A-Z0-9]+)_/i', $user->TenDangNhap, $m)) {
                $maBoMon = $m[1];
            }
            $detaiBoMon = $deTai->giangVien?->MaBoMon ?? $deTai->hocPhanRef?->MaBoMon;
            return $maBoMon && $detaiBoMon && ($maBoMon === $detaiBoMon || 'BM_' . $maBoMon === $detaiBoMon || $maBoMon === 'BM_' . $detaiBoMon);
        }

        // Giảng viên: chỉ xem đề tài do mình đề xuất HOẶC được phân công phản biện
        if ($role === 'Giảng viên') {
            $gv = GiangVien::getLoggedInGiangVien($user);
            if (!$gv) return false;

            if ($deTai->MaGV === $gv->MaGV) {
                return true;
            }

            // Được phân công phản biện đề tài này
            return $deTai->phanCongPhanBiens()->where('MaGV', $gv->MaGV)->exists();
        }

        // Sinh viên: chỉ xem đề tài đã công bố HOẶC nhóm mình đã đăng ký
        if ($role === 'Sinh viên') {
            if ($deTai->TrangThai === 'Đã công bố') {
                return true;
            }

            $sv = SinhVien::where('MaTK', $user->MaTK)->first();
            if (!$sv) return false;

            return $deTai->dangKyDeTais()->whereHas('nhom.thanhViens', function($q) use ($sv) {
                $q->where('MaSV', $sv->MaSV)->where('TrangThai', 'da_tham_gia');
            })->exists();
        }

        return false;
    }

    /**
     * Chỉ GV đề xuất đề tài mới được sửa nó (và đề tài chưa bị khóa sau duyệt/từ chối).
     */
    public function update(TaiKhoan $user, DeTai $deTai): bool
    {
        $user->loadMissing('vaiTro');
        $role = $user->vaiTro->TenVaiTro ?? '';

        if (in_array($role, ['Admin', 'Giáo vụ'])) {
            return true;
        }

        $gv = GiangVien::getLoggedInGiangVien($user);
        if (!$gv || $deTai->MaGV !== $gv->MaGV) {
            return false;
        }

        // Nếu đề tài đã bị từ chối thì không cho sửa
        if ($deTai->TrangThai === 'Từ chối') {
            return false;
        }

        return true;
    }

    /**
     * Chỉ GV đề xuất mới được xóa (và đề tài chưa có nhóm đăng ký hoặc duyệt chính thức).
     */
    public function delete(TaiKhoan $user, DeTai $deTai): bool
    {
        $user->loadMissing('vaiTro');
        $role = $user->vaiTro->TenVaiTro ?? '';

        if (in_array($role, ['Admin', 'Giáo vụ'])) {
            return true;
        }

        $gv = GiangVien::getLoggedInGiangVien($user);
        if (!$gv || $deTai->MaGV !== $gv->MaGV) {
            return false;
        }

        if ($deTai->TrangThai === 'Từ chối') {
            return false;
        }

        // Đã có nhóm đăng ký hoặc đã được phê duyệt chính thức thì không cho xóa
        if ($deTai->dangKyDeTais()->whereIn('TrangThai', ['Chờ duyệt', 'Đã duyệt'])->exists()) {
            return false;
        }

        if (in_array($deTai->TrangThai, ['Đã duyệt', 'Trưởng khoa đã duyệt', 'Đã công bố']) || !empty($deTai->NgayDuyetKhoa)) {
            return false;
        }

        return true;
    }
}
