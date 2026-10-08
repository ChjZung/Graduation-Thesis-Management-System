<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HocPhan extends Model
{
    use HasFactory;

    protected $table = 'HocPhan';
    protected $primaryKey = 'MaHocPhan';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaHocPhan',
        'TenHocPhan',
        'SoTinChi',
        'SoTietLT',
        'SoTietTH',
        'SoTietKhac',
        'MaKhoa',
        'MaBoMon',
        'LoaiHocPhan',
        'TrangThai',
        'MoTa',
    ];

    public $timestamps = true;

    public function khoa()
    {
        return $this->belongsTo(Khoa::class, 'MaKhoa', 'MaKhoa');
    }

    public function boMon()
    {
        return $this->belongsTo(BoMon::class, 'MaBoMon', 'MaBoMon');
    }

    public function nhoms()
    {
        return $this->hasMany(Nhom::class, 'MaHocPhan', 'MaHocPhan');
    }

    public function deTais()
    {
        return $this->hasMany(DeTai::class, 'MaHocPhan', 'MaHocPhan');
    }

    public function hocKies()
    {
        return $this->belongsToMany(HocKy::class, 'HocPhan_HocKy', 'MaHocPhan', 'MaHocKy')
                    ->withPivot(['id', 'TrangThai', 'GhiChu'])
                    ->withTimestamps();
    }

    public function hocPhanHocKies()
    {
        return $this->hasMany(HocPhanHocKy::class, 'MaHocPhan', 'MaHocPhan');
    }

    public function isDungChung(): bool
    {
        return empty($this->MaBoMon);
    }

    public function getTenBoMonHienThiAttribute(): string
    {
        return $this->boMon ? $this->boMon->TenBoMon : 'Học phần dùng chung';
    }
}
