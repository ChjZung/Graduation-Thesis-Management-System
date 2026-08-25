<?php

namespace App\Policies;

use App\Models\GiangVien;
use App\Models\ThanhVienHoiDong;
use App\Models\TaiKhoan;
use App\Models\HoSoBaoVe;

class ChamDiemPolicy
{
    /**
     * GV chỉ được chấm điểm nếu thuộc danh sách Hội đồng bảo vệ của nhóm đó.
     */
    public function grade(TaiKhoan $user, HoSoBaoVe $hoSo): bool
    {
        $gv = GiangVien::where('MaTK', $user->MaTK)->first();
        if (!$gv || !$hoSo->MaHoiDong) return false;

        return ThanhVienHoiDong::where('MaHoiDong', $hoSo->MaHoiDong)
            ->where('MaGV', $gv->MaGV)
            ->exists();
    }
}
