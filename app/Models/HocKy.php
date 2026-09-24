<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HocKy extends Model
{
    use HasFactory;

    protected $table = 'HocKy';
    protected $primaryKey = 'MaHocKy';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaHocKy',
        'TenHocKy',
        'NamHoc',
        'NgayDiHoc',
        'NgayBatDau',
        'NgayKetThuc',
        'TrangThai',
    ];

    public $timestamps = true;

    // Backward compatibility for NgayBatDau
    public function getNgayBatDauAttribute()
    {
        return $this->attributes['NgayDiHoc'] ?? null;
    }

    public function setNgayBatDauAttribute($value)
    {
        $this->attributes['NgayDiHoc'] = $value;
    }

    public function deTais()
    {
        return $this->hasMany(DeTai::class, 'MaHocKy', 'MaHocKy');
    }

    public function danhSachSVDuDieuKiens()
    {
        return $this->hasMany(DanhSachSVDuDieuKien::class, 'MaHocKy', 'MaHocKy');
    }

    public function chiTieuHuongDans()
    {
        return $this->hasMany(ChiTieuHuongDan::class, 'MaHocKy', 'MaHocKy');
    }

    public function keHoachKhoaLuans()
    {
        return $this->hasMany(KeHoachKhoaLuan::class, 'MaHocKy', 'MaHocKy');
    }

    public function hoiDongs()
    {
        return $this->hasMany(HoiDong::class, 'MaHocKy', 'MaHocKy');
    }

    public function ketQuaSinhViens()
    {
        return $this->hasMany(KetQuaSinhVien::class, 'MaHocKy', 'MaHocKy');
    }
}
