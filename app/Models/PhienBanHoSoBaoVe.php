<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhienBanHoSoBaoVe extends Model
{
    use HasFactory;

    protected $table = 'phien_ban_ho_so_bao_ve';

    protected $guarded = ['id'];

    protected $casts = [
        'LienKetKhac'     => 'array',
        'ThanhPhanCanSua' => 'array',
        'TyLeTurnitin'    => 'float',
        'NgayNop'         => 'datetime',
        'HanNopLai'       => 'datetime',
        'NgayDanhGia'     => 'datetime',
    ];

    public function hoSoBaoVe()
    {
        return $this->belongsTo(HoSoBaoVe::class, 'MaHoSo', 'MaHoSo');
    }

    public function sinhVienNop()
    {
        return $this->belongsTo(SinhVien::class, 'NguoiNop', 'MaSV');
    }

    public function giangVienDanhGia()
    {
        return $this->belongsTo(GiangVien::class, 'NguoiDanhGia', 'MaGV');
    }
}
