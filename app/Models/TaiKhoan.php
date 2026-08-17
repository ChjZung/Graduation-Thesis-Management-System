<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class TaiKhoan extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'tai_khoans';
    protected $primaryKey = 'MaTK';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaTK', 'MaVaiTro', 'TenDangNhap', 'MatKhau', 'TrangThai', 'SoLanDangNhapSai', 'BatBuocDoiMatKhau', 'LanDangNhapCuoi', 'NgayKhoa', 'remember_token'
    ];

    protected $hidden = [
        'MatKhau',
        'remember_token',
    ];

    public $timestamps = true;

    public function getAuthPasswordName()
    {
        return 'MatKhau';
    }

    public function getAuthPassword()
    {
        return $this->MatKhau;
    }

    public function vaiTro()
    {
        return $this->belongsTo(VaiTro::class, 'MaVaiTro', 'MaVaiTro');
    }

    public function giaoVu()
    {
        return $this->hasOne(GiaoVu::class, 'MaTK', 'MaTK');
    }

    public function giangVien()
    {
        return $this->hasOne(GiangVien::class, 'MaTK', 'MaTK');
    }

    public function sinhVien()
    {
        return $this->hasOne(SinhVien::class, 'MaTK', 'MaTK');
    }
}

