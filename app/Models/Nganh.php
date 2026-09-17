<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nganh extends Model
{
    use HasFactory;

    protected $table = 'Nganh';
    protected $primaryKey = 'MaNganh';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaNganh',
        'TenNganh',
        'MaKhoa',
    ];

    public function khoa()
    {
        return $this->belongsTo(Khoa::class, 'MaKhoa', 'MaKhoa');
    }

    public function chuyenNganhs()
    {
        return $this->hasMany(ChuyenNganh::class, 'MaNganh', 'MaNganh');
    }

    public function lops()
    {
        return $this->hasMany(Lop::class, 'MaNganh', 'MaNganh');
    }

    public function Lop()
    {
        return $this->lops();
    }

    public function sinhViens()
    {
        return $this->hasMany(SinhVien::class, 'MaNganh', 'MaNganh');
    }

    public function SinhVien()
    {
        return $this->sinhViens();
    }
}
