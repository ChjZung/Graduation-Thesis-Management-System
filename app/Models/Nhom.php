<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nhom extends Model
{
    use HasFactory;

    protected $table = 'nhoms';
    protected $primaryKey = 'MaNhom';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaNhom', 'MaDeTai', 'MaTruongNhom', 'TenNhom', 'TrangThai', 'NgayTao'
    ];

    public $timestamps = true;

    public function deTai()
    {
        return $this->belongsTo(DeTai::class, 'MaDeTai', 'MaDeTai');
    }

    public function truongNhom()
    {
        return $this->belongsTo(SinhVien::class, 'MaTruongNhom', 'MaSV');
    }

    public function thanhViens()
    {
        return $this->hasMany(ThanhVienNhom::class, 'MaNhom', 'MaNhom');
    }

    public function baoCaos()
    {
        return $this->hasMany(BaoCaoTienDo::class, 'MaNhom', 'MaNhom');
    }

    public function dangKyDeTai()
    {
        return $this->hasOne(DangKyDeTai::class, 'MaNhom', 'MaNhom');
    }
}

