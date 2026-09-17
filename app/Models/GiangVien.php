<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiangVien extends Model
{
    use HasFactory;

    protected $table = 'GiangVien';
    protected $primaryKey = 'MaGV';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaGV',
        'MaTK',
        'HoTen',
        'NgaySinh',
        'GioiTinh',
        'Email',
        'SoDienThoai',
        'HocHam',
        'HocVi',
        'TrangThai',
        'MaBoMon',
    ];

    public $timestamps = true;

    // Backward compatibility for Sdt
    public function getSdtAttribute()
    {
        return $this->attributes['SoDienThoai'] ?? null;
    }

    public function setSdtAttribute($value)
    {
        $this->attributes['SoDienThoai'] = $value;
    }

    public function boMon()
    {
        return $this->belongsTo(BoMon::class, 'MaBoMon', 'MaBoMon');
    }

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'MaTK', 'MaTK');
    }

    public function deTais()
    {
        return $this->hasMany(DeTai::class, 'MaGV', 'MaGV');
    }

    public function chiTieuHuongDans()
    {
        return $this->hasMany(ChiTieuHuongDan::class, 'MaGV', 'MaGV');
    }

    public function phanCongPhanBiens()
    {
        return $this->hasMany(PhanCongPhanBien::class, 'MaGV', 'MaGV');
    }

    public function lichGaps()
    {
        return $this->hasMany(LichGapHuongDan::class, 'MaGV', 'MaGV');
    }

    public function thanhVienHoiDongs()
    {
        return $this->hasMany(ThanhVienHoiDong::class, 'MaGV', 'MaGV');
    }

    public function phieuChamDiems()
    {
        return $this->hasMany(PhieuChamDiem::class, 'MaGV', 'MaGV');
    }
}
