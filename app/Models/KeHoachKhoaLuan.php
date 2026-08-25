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
        'MaKeHoach', 'MaKhoa', 'MaBoMon', 'MaHocKy', 'NamHoc', 'MaGVu', 'NguoiLap',
        'TenKeHoach', 'NoiDung', 'NgayBatDau', 'NgayKetThuc', 'TrangThai',
        'NgayTao', 'NgayCongBo'
    ];

    public $timestamps = true;

    public function hocKy()
    {
        return $this->belongsTo(HocKy::class, 'MaHocKy', 'MaHocKy');
    }

    public function khoa()
    {
        return $this->belongsTo(Khoa::class, 'MaKhoa', 'MaKhoa');
    }

    public function boMon()
    {
        return $this->belongsTo(BoMon::class, 'MaBoMon', 'MaBoMon');
    }

    public function mocThoiGians()
    {
        return $this->hasMany(MocThoiGianKhoaLuan::class, 'MaKeHoach', 'MaKeHoach')->orderBy('ThuTu', 'asc');
    }

    public function svDuDieuKien()
    {
        return $this->hasMany(DanhSachSVDuDieuKien::class, 'MaKeHoach', 'MaKeHoach');
    }
}
