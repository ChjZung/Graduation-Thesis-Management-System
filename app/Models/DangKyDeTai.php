<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DangKyDeTai extends Model
{
    use HasFactory;

    protected $table = 'dang_ky_de_tais';
    protected $primaryKey = 'MaDangKy';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaDangKy', 'MaNhom', 'MaDeTai', 'MaGVHuongDan', 'NgayDangKy', 'TrangThai', 'NgayDuyet', 'GhiChu', 'LyDoTuChoi'
    ];

    public $timestamps = true;

    public function nhom()
    {
        return $this->belongsTo(Nhom::class, 'MaNhom', 'MaNhom');
    }

    public function deTai()
    {
        return $this->belongsTo(DeTai::class, 'MaDeTai', 'MaDeTai');
    }

    public function giangVienHuongDan()
    {
        return $this->belongsTo(GiangVien::class, 'MaGVHuongDan', 'MaGV');
    }
}

