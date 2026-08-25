<?php

namespace App\Policies;

use App\Models\GiangVien;
use App\Models\DeTai;
use App\Models\TaiKhoan;

class DeTaiPolicy
{
    /**
     * Chỉ GV đề xuất đề tài mới được sửa/xóa nó.
     */
    public function update(TaiKhoan $user, DeTai $deTai): bool
    {
        $gv = GiangVien::where('MaTK', $user->MaTK)->first();
        return $gv && $deTai->MaGV === $gv->MaGV;
    }

    public function delete(TaiKhoan $user, DeTai $deTai): bool
    {
        return $this->update($user, $deTai);
    }
}
