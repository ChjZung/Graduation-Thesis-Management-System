<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YeuCauDoiMatKhau extends Model
{
    use HasFactory;

    protected $table = 'YeuCauDoiMatKhau';
    protected $primaryKey = 'MaYeuCau';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'TenDangNhap',
        'HoTen',
        'Role',
        'Email',
        'LyDo',
        'TrangThai',
        'NgayGui',
        'NgayDuyet',
        'NguoiDuyet',
    ];

    protected function casts(): array
    {
        return [
            'NgayGui' => 'datetime',
            'NgayDuyet' => 'datetime',
        ];
    }

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'TenDangNhap', 'TenDangNhap');
    }
}
