<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Khoa extends Model
{
    use HasFactory;

    protected $table = 'Khoa';
    protected $primaryKey = 'MaKhoa';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaKhoa',
        'TenKhoa',
        'TruongKhoa',
    ];

    public function boMons()
    {
        return $this->hasMany(BoMon::class, 'MaKhoa', 'MaKhoa');
    }

    public function BoMon()
    {
        return $this->boMons();
    }

    public function nganhs()
    {
        return $this->hasMany(Nganh::class, 'MaKhoa', 'MaKhoa');
    }

    public function Nganh()
    {
        return $this->nganhs();
    }

    public function lops()
    {
        return $this->hasMany(Lop::class, 'MaKhoa', 'MaKhoa');
    }

    public function Lop()
    {
        return $this->lops();
    }

    public function sinhViens()
    {
        return $this->hasMany(SinhVien::class, 'MaKhoa', 'MaKhoa');
    }

    public function SinhVien()
    {
        return $this->sinhViens();
    }

    public function giaoVus()
    {
        return $this->hasMany(GiaoVu::class, 'MaKhoa', 'MaKhoa');
    }

    public function GiaoVu()
    {
        return $this->giaoVus();
    }
}
