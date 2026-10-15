<?php

namespace App\Policies;

use App\Models\GiangVien;
use App\Models\ThongBao;
use App\Models\TaiKhoan;
use App\Models\SinhVien;
use App\Models\ThanhVienNhom;

class ThongBaoPolicy
{
    /**
     * Xác định người dùng có quyền xem thông báo này hay không.
     */
    public function view(TaiKhoan $user, ThongBao $thongBao): bool
    {
        $user->loadMissing('vaiTro');
        $role = $user->vaiTro->TenVaiTro ?? '';

        // Giáo vụ / Admin / Trưởng khoa / Trưởng bộ môn được xem mọi thông báo
        if (in_array($role, ['Admin', 'Giáo vụ', 'Trưởng khoa', 'Trưởng bộ môn'])) {
            return true;
        }

        $doiTuong = $thongBao->DoiTuongNhan ?? '';

        // Giảng viên
        if ($role === 'Giảng viên') {
            $gv = GiangVien::getLoggedInGiangVien($user);

            // Giảng viên là người tạo thông báo
            if ($gv && $thongBao->MaGV === $gv->MaGV) {
                return true;
            }

            // Thông báo gửi tới Giảng viên hoặc Toàn trường
            if (empty($doiTuong) || in_array($doiTuong, ['Giảng viên', 'Tất cả', 'Toàn thể', 'Toàn trường', 'Toàn thể GV và SV'])) {
                return true;
            }

            // Giảng viên hướng dẫn được xem thông báo kế hoạch và danh sách SV đủ điều kiện
            if (str_starts_with($thongBao->MaThongBao, 'TB_DK_') || str_starts_with($thongBao->MaThongBao, 'TB_KH_') || str_contains($thongBao->TieuDe ?? '', 'đề tài') || str_contains($thongBao->TieuDe ?? '', 'Sinh viên đăng ký')) {
                return true;
            }

            // Thông báo gửi trực tiếp đích danh tài khoản
            if ($doiTuong === $user->MaTK || str_contains($doiTuong, $user->MaTK)) {
                return true;
            }

            return false;
        }

        // Sinh viên
        if ($role === 'Sinh viên') {
            // Thông báo kế hoạch khóa luận, danh sách sinh viên đủ điều kiện, hoặc công bố đề tài/nhóm
            if (str_starts_with($thongBao->MaThongBao, 'TB_DK_') || 
                str_starts_with($thongBao->MaThongBao, 'TB_KH_') || 
                str_starts_with($thongBao->MaThongBao, 'TB_DT_') ||
                str_contains($thongBao->TieuDe ?? '', 'đề tài') ||
                str_contains($thongBao->TieuDe ?? '', 'Sinh viên đăng ký')) {
                return true;
            }

            // Thông báo gửi tới Sinh viên hoặc Toàn trường hoặc không giới hạn đối tượng
            if (empty($doiTuong) || in_array($doiTuong, ['Sinh viên', 'Tất cả', 'Toàn thể', 'Toàn trường', 'Toàn thể sinh viên', 'Toàn thể GV và SV'])) {
                return true;
            }

            $sv = SinhVien::where('MaTK', $user->MaTK)->orWhere('MaSV', $user->TenDangNhap)->first();
            $maSV = $sv?->MaSV ?? $user->TenDangNhap;

            // Thông báo gửi trực tiếp đích danh tài khoản sinh viên hoặc mã sinh viên
            if ($doiTuong === $user->MaTK || $doiTuong === $maSV || str_contains($doiTuong, $user->MaTK) || str_contains($doiTuong, $maSV)) {
                return true;
            }

            // Thông báo gửi tới nhóm mà sinh viên tham gia
            if ($sv) {
                $nhomCodes = ThanhVienNhom::where('MaSV', $sv->MaSV)
                    ->where('TrangThai', 'da_tham_gia')
                    ->pluck('MaNhom')
                    ->toArray();

                if (str_contains($doiTuong, 'Tất cả các nhóm')) {
                    return true;
                }

                foreach ($nhomCodes as $code) {
                    if (str_contains($doiTuong, $code)) {
                        return true;
                    }
                }
            }

            return false;
        }

        return false;
    }
}
