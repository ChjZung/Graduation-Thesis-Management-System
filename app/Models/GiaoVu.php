<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiaoVu extends Model
{
    use HasFactory;

    protected $table = 'GiaoVu';
    protected $primaryKey = 'MaGVu';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaGVu',
        'MaTK',
        'HoTen',
        'Email',
        'SoDienThoai',
        'ChucVu',
        'MaKhoa',
    ];

    public $timestamps = true;

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'MaTK', 'MaTK');
    }

    public function khoa()
    {
        return $this->belongsTo(Khoa::class, 'MaKhoa', 'MaKhoa');
    }

    public function keHoachKhoaLuans()
    {
        return $this->hasMany(KeHoachKhoaLuan::class, 'MaGVu', 'MaGVu');
    }

    public function thongBaos()
    {
        return $this->hasMany(ThongBao::class, 'MaGVu', 'MaGVu');
    }

    public function hoSoBaoVes()
    {
        return $this->hasMany(HoSoBaoVe::class, 'MaGVu', 'MaGVu');
    }
}
