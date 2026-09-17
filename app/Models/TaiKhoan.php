<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class TaiKhoan extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'TaiKhoan';
    protected $primaryKey = 'MaTK';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaTK',
        'MaVaiTro',
        'TenDangNhap',
        'MatKhau',
        'TrangThai',
        'SoLanDangNhapSai',
        'BatBuocDoiMatKhau',
        'TrangThaiMatKhau',
        'LanDangNhapDau',
        'NgayDoiMatKhau',
        'LanDangNhapCuoi',
        'NgayKhoa',
        'remember_token',
        // Compatibility aliases
        'password_status',
        'password_changed_at',
        'first_login_at',
    ];

    protected $hidden = [
        'MatKhau',
        'remember_token',
    ];

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'LanDangNhapDau'    => 'datetime',
            'NgayDoiMatKhau'    => 'datetime',
            'LanDangNhapCuoi'   => 'datetime',
            'NgayKhoa'          => 'datetime',
            'TrangThai'         => 'boolean',
            'BatBuocDoiMatKhau' => 'boolean',
        ];
    }

    // Backward compatibility accessors/mutators for existing code
    public function getPasswordStatusAttribute()
    {
        return $this->attributes['TrangThaiMatKhau'] ?? 'INITIAL';
    }

    public function setPasswordStatusAttribute($value)
    {
        $this->attributes['TrangThaiMatKhau'] = $value;
    }

    public function getFirstLoginAtAttribute()
    {
        return $this->attributes['LanDangNhapDau'] ?? null;
    }

    public function setFirstLoginAtAttribute($value)
    {
        $this->attributes['LanDangNhapDau'] = $value;
    }

    public function getPasswordChangedAtAttribute()
    {
        return $this->attributes['NgayDoiMatKhau'] ?? null;
    }

    public function setPasswordChangedAtAttribute($value)
    {
        $this->attributes['NgayDoiMatKhau'] = $value;
    }

    public function getAuthPasswordName()
    {
        return 'MatKhau';
    }

    public function getAuthPassword()
    {
        return $this->MatKhau;
    }

    public function isPasswordInitial(): bool
    {
        return ($this->TrangThaiMatKhau === 'INITIAL' || $this->BatBuocDoiMatKhau === true);
    }

    public function isPasswordActive(): bool
    {
        return ($this->TrangThaiMatKhau === 'ACTIVE' && !$this->BatBuocDoiMatKhau);
    }

    public function isPasswordExpired(): bool
    {
        return ($this->TrangThaiMatKhau === 'EXPIRED');
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
