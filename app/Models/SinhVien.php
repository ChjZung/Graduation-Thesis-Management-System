<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SinhVien extends Model
{
    use HasFactory;

    protected $table = 'sinh_viens';
    protected $primaryKey = 'MaSV';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaSV', 'MaTK', 'MaLop', 'MaSoSinhVien', 'HoTen', 'NgaySinh', 'GioiTinh', 'Email', 'SoDienThoai', 'NgayNhapHoc', 'KhoaHoc', 'SoTinChiTichLuy', 'DiemTichLuy', 'TrangThai'
    ];

    public $timestamps = true;

    public function lop()
    {
        return $this->belongsTo(Lop::class, 'MaLop', 'MaLop');
    }

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'MaTK', 'MaTK');
    }
}

