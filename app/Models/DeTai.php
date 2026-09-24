<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeTai extends Model
{
    use HasFactory;

    protected $table = 'DeTai';
    protected $primaryKey = 'MaDeTai';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaDeTai',
        'TenDeTai',
        'MoTa',
        'YeuCau',
        'LinhVuc',
        'SoLuongSinhVienToiDa',
        'FileDeCuong',
        'MaNganh',
        'HocPhan',
        'TrangThai',
        'LyDoTuChoi',
        'NgayDeXuat',
        'NgayDuyet',
        'MaGV',
        'MaHocKy',
    ];

    public $timestamps = true;

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'MaGV', 'MaGV');
    }

    public function hocKy()
    {
        return $this->belongsTo(HocKy::class, 'MaHocKy', 'MaHocKy');
    }

    public function nganh()
    {
        return $this->belongsTo(Nganh::class, 'MaNganh', 'MaNganh');
    }

    public function chiTietDuyets()
    {
        return $this->hasMany(ChiTietDuyetDeTai::class, 'MaDeTai', 'MaDeTai');
    }

    public function phanCongPhanBiens()
    {
        return $this->hasMany(PhanCongPhanBien::class, 'MaDeTai', 'MaDeTai');
    }

    public function phieuDangKys()
    {
        return $this->hasMany(PhieuDangKy::class, 'MaDeTai', 'MaDeTai');
    }

    // Alias for backward compatibility
    public function dangKyDeTais()
    {
        return $this->phieuDangKys();
    }

    public function baoCaoTienDos()
    {
        return $this->hasMany(BaoCaoTienDo::class, 'MaDeTai', 'MaDeTai');
    }

    public function taiLieuNops()
    {
        return $this->hasMany(TaiLieuNop::class, 'MaDeTai', 'MaDeTai');
    }

    public function hoiDongs()
    {
        return $this->hasMany(HoiDong::class, 'MaDeTai', 'MaDeTai');
    }

    public function hoSoBaoVes()
    {
        return $this->hasMany(HoSoBaoVe::class, 'MaDeTai', 'MaDeTai');
    }

    public function phieuChamDiems()
    {
        return $this->hasMany(PhieuChamDiem::class, 'MaDeTai', 'MaDeTai');
    }
}
