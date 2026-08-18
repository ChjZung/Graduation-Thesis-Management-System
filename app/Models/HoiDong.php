<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HoiDong extends Model
{
    use HasFactory;

    protected $table = 'hoi_dongs';
    protected $primaryKey = 'MaHoiDong';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaHoiDong', 'TenHoiDong', 'ThoiGianBatDau', 'ThoiGianKetThuc',
        'DiaDiem', 'TrangThai', 'GhiChu',
    ];

    protected $casts = [
        'ThoiGianBatDau' => 'datetime',
        'ThoiGianKetThuc' => 'datetime',
    ];

    public $timestamps = true;

    public function thanhViens()
    {
        return $this->hasMany(ThanhVienHoiDong::class, 'MaHoiDong', 'MaHoiDong');
    }

    public function giangViens()
    {
        return $this->belongsToMany(
            GiangVien::class,
            'thanh_vien_hoi_dongs',
            'MaHoiDong',
            'MaGV'
        )->withPivot('VaiTro');
    }

    public function hoSoBaoVes()
    {
        return $this->hasMany(HoSoBaoVe::class, 'MaHoiDong', 'MaHoiDong');
    }

    public function chiTietDiems()
    {
        return $this->hasMany(ChiTietDiemHoiDong::class, 'MaHoiDong', 'MaHoiDong');
    }
}
