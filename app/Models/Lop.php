<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lop extends Model
{
    use HasFactory;

    protected $table = 'Lop';
    protected $primaryKey = 'MaLop';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaLop',
        'TenLop',
        'KhoaHoc',
        'MaNganh',
        'MaKhoa',
    ];

    public function nganh()
    {
        return $this->belongsTo(Nganh::class, 'MaNganh', 'MaNganh');
    }

    public function khoa()
    {
        return $this->belongsTo(Khoa::class, 'MaKhoa', 'MaKhoa');
    }

    public function sinhViens()
    {
        return $this->hasMany(SinhVien::class, 'MaLop', 'MaLop');
    }

    public function SinhVien()
    {
        return $this->sinhViens();
    }
}
