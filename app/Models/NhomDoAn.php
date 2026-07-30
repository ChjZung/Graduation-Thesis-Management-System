<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class NhomDoAn extends Model
{
    protected $table = 'nhom_do_ans';
    protected $primaryKey = 'MaNhom';
    protected $fillable = ['TenNhom', 'MaHocKy', 'MaMon', 'TruongNhom', 'TrangThai'];

    public function hocKy() { return $this->belongsTo(HocKy::class, 'MaHocKy', 'MaHocKy'); }
    public function monHoc() { return $this->belongsTo(MonHoc::class, 'MaMon', 'MaMon'); }
    public function sinhVienTruongNhom() { return $this->belongsTo(SinhVien::class, 'TruongNhom', 'MaSV'); }
    public function thanhVienNhoms() { return $this->hasMany(ThanhVienNhom::class, 'MaNhom', 'MaNhom'); }
    public function loiMois() { return $this->hasMany(LoiMoiNhom::class, 'MaNhom', 'MaNhom'); }
    public function dangKyDeTai() { return $this->hasOne(DangKyDeTai::class, 'MaNhom', 'MaNhom'); }
    public function chamDiem() { return $this->hasOne(ChamDiem::class, 'MaNhom', 'MaNhom'); }
    public function sanPhams() { return $this->hasMany(SanPham::class, 'MaNhom', 'MaNhom'); }
    public function baoCaoTienDos() { return $this->hasMany(BaoCaoTienDo::class, 'MaNhom', 'MaNhom'); }
    public function baoCaos() { return $this->hasMany(BaoCaoTienDo::class, 'MaNhom', 'MaNhom'); }
}