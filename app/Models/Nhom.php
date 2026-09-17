<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nhom extends Model
{
    use HasFactory;

    protected $table = 'Nhom';
    protected $primaryKey = 'MaNhom';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaNhom',
        'TenNhom',
        'TrangThai',
        'NgayTao',
    ];

    public $timestamps = true;

    public function thanhVienNhoms()
    {
        return $this->hasMany(ThanhVienNhom::class, 'MaNhom', 'MaNhom');
    }

    public function thanhViens()
    {
        return $this->thanhVienNhoms();
    }

    public function sinhViens()
    {
        return $this->belongsToMany(SinhVien::class, 'ThanhVienNhom', 'MaNhom', 'MaSV')
                    ->withPivot('VaiTro', 'NgayThamGia')
                    ->withTimestamps();
    }

    public function truongNhom()
    {
        return $this->hasOneThrough(SinhVien::class, ThanhVienNhom::class, 'MaNhom', 'MaSV', 'MaNhom', 'MaSV')
                    ->where('ThanhVienNhom.VaiTro', 'like', '%trưởng%');
    }

    public function nhomTruong()
    {
        $tv = $this->thanhVienNhoms()->where('VaiTro', 'like', '%trưởng%')->first();
        return $tv ? $tv->sinhVien : null;
    }

    public function getMaTruongNhomAttribute()
    {
        return $this->thanhVienNhoms()->where('VaiTro', 'like', '%trưởng%')->value('MaSV')
            ?? $this->thanhVienNhoms()->value('MaSV');
    }

    public function getMaDeTaiAttribute()
    {
        return $this->dangKyDeTai?->MaDeTai;
    }

    public function phieuDangKys()
    {
        return $this->hasMany(PhieuDangKy::class, 'MaNhom', 'MaNhom');
    }

    public function dangKyDeTais()
    {
        return $this->phieuDangKys();
    }

    public function dangKyDeTai()
    {
        return $this->hasOne(PhieuDangKy::class, 'MaNhom', 'MaNhom')->latestOfMany('created_at');
    }

    public function deTai()
    {
        return $this->hasOneThrough(DeTai::class, PhieuDangKy::class, 'MaNhom', 'MaDeTai', 'MaNhom', 'MaDeTai');
    }

    public function baoCaos()
    {
        return $this->hasManyThrough(BaoCaoTienDo::class, PhieuDangKy::class, 'MaNhom', 'MaDeTai', 'MaNhom', 'MaDeTai');
    }

    public function lichGaps()
    {
        return $this->hasMany(LichGapHuongDan::class, 'MaNhom', 'MaNhom');
    }

    public function hoSoBaoVes()
    {
        return $this->hasMany(HoSoBaoVe::class, 'MaNhom', 'MaNhom');
    }

    public function hoSoBaoVe()
    {
        return $this->hasOne(HoSoBaoVe::class, 'MaNhom', 'MaNhom')->latestOfMany('created_at');
    }
}
