<?php

namespace App\Policies;

use App\Models\GiangVien;
use App\Models\BaoCaoTienDo;
use App\Models\TaiKhoan;
use App\Models\DangKyDeTai;

class BaoCaoPolicy
{
    /**
     * GV chỉ được nhận xét báo cáo của nhóm mình hướng dẫn.
     */
    public function review(TaiKhoan $user, BaoCaoTienDo $baoCao): bool
    {
        $gv = GiangVien::where('MaTK', $user->MaTK)->first();
        if (!$gv) return false;

        // Kiểm tra GV có phải GVHD của nhóm nộp báo cáo không
        return DangKyDeTai::where('MaNhom', $baoCao->MaNhom)
            ->where('MaGVHuongDan', $gv->MaGV)
            ->where('TrangThai', 'Đã duyệt')
            ->exists();
    }
}
