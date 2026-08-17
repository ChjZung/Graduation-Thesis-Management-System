<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeHoachKhoaLuan extends Model
{
    use HasFactory;

    protected $table = 'ke_hoach_khoa_luans';
    protected $primaryKey = 'MaKeHoach';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaKeHoach', 'MaHocKy', 'MaGVu', 'TenKeHoach', 'NoiDung', 'TrangThai', 'NgayTao', 'NgayCongBo'
    ];

    public $timestamps = true;

    public function hocKy()
    {
        return $this->belongsTo(HocKy::class, 'MaHocKy', 'MaHocKy');
    }

    public function mocThoiGians()
    {
        return $this->hasMany(MocThoiGianKhoaLuan::class, 'MaKeHoach', 'MaKeHoach')->orderBy('NgayBatDau', 'asc');
    }

    public function svDuDieuKien()
    {
        return $this->hasMany(DanhSachSVDuDieuKien::class, 'MaKeHoach', 'MaKeHoach');
    }
}

