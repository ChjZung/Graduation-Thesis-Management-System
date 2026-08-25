<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeTai extends Model
{
    use HasFactory;

    protected $table = 'de_tais';
    protected $primaryKey = 'MaDeTai';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaDeTai', 'MaGV', 'TenDeTai', 'MoTa', 'YeuCau', 'LinhVuc', 'SoLuongSinhVienToiDa', 'MaHocKy', 'TrangThai', 'NgayDeXuat', 'NgayDuyet', 'LyDoTuChoi'
    ];

    public $timestamps = true;

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'MaGV', 'MaGV');
    }

    public function dangKyDeTais()
    {
        return $this->hasMany(DangKyDeTai::class, 'MaDeTai', 'MaDeTai');
    }

    public function nhoms()
    {
        return $this->hasMany(Nhom::class, 'MaDeTai', 'MaDeTai');
    }
}


