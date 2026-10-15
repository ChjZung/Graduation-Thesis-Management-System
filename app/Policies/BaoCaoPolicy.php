<?php

namespace App\Policies;

use App\Models\GiangVien;
use App\Models\BaoCaoTienDo;
use App\Models\TaiKhoan;
use App\Models\SinhVien;
use App\Models\DangKyDeTai;
use App\Models\ThanhVienNhom;

class BaoCaoPolicy
{
    /**
     * Xác định người dùng có thể xem báo cáo tiến độ không.
     */
    public function view(TaiKhoan $user, BaoCaoTienDo $baoCao): bool
    {
        $user->loadMissing('vaiTro');
        $role = $user->vaiTro->TenVaiTro ?? '';

        if (in_array($role, ['Admin', 'Giáo vụ'])) {
            return true;
        }

        // Trưởng khoa / Trưởng bộ môn phụ trách đề tài
        if (in_array($role, ['Trưởng khoa', 'Trưởng bộ môn'])) {
            $gv = GiangVien::getLoggedInGiangVien($user);
            $baoCao->loadMissing('deTai.giangVien.boMon.khoa');
            $dt = $baoCao->deTai;
            if (!$dt) return false;

            if ($role === 'Trưởng khoa') {
                $maKhoa = $gv?->boMon?->MaKhoa;
                if (!$maKhoa && preg_match('/^TK_([A-Z0-9]+)_/i', $user->TenDangNhap, $m)) {
                    $maKhoa = $m[1];
                }
                return $maKhoa && $dt->giangVien?->boMon?->MaKhoa === $maKhoa;
            }

            if ($role === 'Trưởng bộ môn') {
                $maBoMon = $gv?->MaBoMon;
                if (!$maBoMon && preg_match('/^TBM_(?:BM_)?([A-Z0-9]+)_/i', $user->TenDangNhap, $m)) {
                    $maBoMon = $m[1];
                }
                $dtBoMon = $dt->giangVien?->MaBoMon;
                return $maBoMon && ($dtBoMon === $maBoMon || 'BM_' . $maBoMon === $dtBoMon || $maBoMon === 'BM_' . $dtBoMon);
            }
        }

        // GV hướng dẫn của đề tài
        if ($role === 'Giảng viên') {
            $gv = GiangVien::getLoggedInGiangVien($user);
            if (!$gv) return false;

            $baoCao->loadMissing('deTai');
            return $baoCao->deTai && $baoCao->deTai->MaGV === $gv->MaGV;
        }

        // Sinh viên thuộc nhóm nộp báo cáo
        if ($role === 'Sinh viên') {
            $sv = SinhVien::where('MaTK', $user->MaTK)->first();
            if (!$sv) return false;

            return ThanhVienNhom::where('MaSV', $sv->MaSV)
                ->where('TrangThai', 'da_tham_gia')
                ->where(function($q) use ($baoCao) {
                    $q->where('ThanhVienNhom.MaNhom', $baoCao->MaNhom)
                      ->orWhereHas('nhom.deTai', function($dq) use ($baoCao) {
                          $dq->where('DeTai.MaDeTai', $baoCao->MaDeTai);
                      });
                })->exists();
        }

        return false;
    }

    /**
     * GV chỉ được nhận xét báo cáo của nhóm mình hướng dẫn.
     */
    public function review(TaiKhoan $user, BaoCaoTienDo $baoCao): bool
    {
        $gv = GiangVien::getLoggedInGiangVien($user);
        if (!$gv) return false;

        $baoCao->loadMissing('deTai');
        if ($baoCao->deTai && $baoCao->deTai->MaGV === $gv->MaGV) {
            return true;
        }

        return DangKyDeTai::where('MaNhom', $baoCao->MaNhom)
            ->where('TrangThai', 'Đã duyệt')
            ->whereHas('deTai', function($q) use ($gv) {
                $q->where('MaGV', $gv->MaGV);
            })
            ->exists();
    }
}
