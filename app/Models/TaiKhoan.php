<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class TaiKhoan extends Authenticatable
{
    use Notifiable;

    protected $table = 'tai_khoans';
    protected $primaryKey = 'MaTK';

    protected $fillable = [
        'TenDangNhap',
        'MatKhau',
        'MaVaiTro',
        'TrangThai',
    ];

    protected $hidden = [
        'MatKhau',
    ];

    // Laravel uses password for Auth by default, we map it to MatKhau
    public function getAuthPassword()
    {
        return $this->MatKhau;
    }

    public function vaiTro()
    {
        return $this->belongsTo(VaiTro::class, 'MaVaiTro', 'MaVaiTro');
    }

    public function sinhVien()
    {
        return $this->hasOne(SinhVien::class, 'MaTK', 'MaTK');
    }

    public function giangVien()
    {
        return $this->hasOne(GiangVien::class, 'MaTK', 'MaTK');
    }
}
