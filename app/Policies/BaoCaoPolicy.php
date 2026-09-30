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
            ->where('TrangThai', 'Đã duyệt')
            ->whereHas('deTai', function($q) use ($gv) {
                $q->where('MaGV', $gv->MaGV);
            })
            ->exists();
    }
}
