<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhieuDangKy extends Model
{
    use HasFactory;

    protected $table = 'PhieuDangKy';
    protected $primaryKey = 'MaDangKy';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaDangKy',
        'NgayDangKy',
        'TrangThai',
        'NgayDuyet',
        'LyDoTuChoi',
        'MaNhom',
        'MaDeTai',
    ];

    public $timestamps = true;

    // Backward compatibility accessor for MaDangKyDeTai
    public function getMaDangKyDeTaiAttribute()
    {
        return $this->attributes['MaDangKy'] ?? null;
    }

    public function setMaDangKyDeTaiAttribute($value)
    {
        $this->attributes['MaDangKy'] = $value;
    }

    public function getMaGVHuongDanAttribute()
    {
        return $this->deTai?->MaGV;
    }

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
        return $this->hasOneThrough(GiangVien::class, DeTai::class, 'MaDeTai', 'MaGV', 'MaDeTai', 'MaGV');
    }
}
