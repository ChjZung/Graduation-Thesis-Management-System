<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoiMoiNhom extends Model
{
    protected $table = 'loi_moi_nhoms';

    protected $fillable = [
        'MaNhom',
        'MaSV_Moi',
        'MaSV_DuocMoi',
        'TrangThai',
        'NgayMoi',
        'NgayPhanHoi',
    ];

    public function nhomDoAn()
    {
        return $this->belongsTo(NhomDoAn::class, 'MaNhom', 'MaNhom');
    }

    public function sinhVienMoi()
    {
        return $this->belongsTo(SinhVien::class, 'MaSV_Moi', 'MaSV');
    }

    public function sinhVienDuocMoi()
    {
        return $this->belongsTo(SinhVien::class, 'MaSV_DuocMoi', 'MaSV');
    }
}
