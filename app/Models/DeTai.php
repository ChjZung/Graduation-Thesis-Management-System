<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DeTai extends Model
{
    protected $table = 'de_tais';
    protected $primaryKey = 'MaDeTai';
    protected $fillable = ['MaTK', 'MaMon', 'MaLop', 'MaHocKy', 'TenDeTai', 'MoTa', 'YeuCau', 'FileTaiLieu', 'TrangThai', 'HanDangKy', 'HanBaoCao', 'HanNopSanPham', 'NgayTao'];

    public function taiKhoan() { return $this->belongsTo(TaiKhoan::class, 'MaTK', 'MaTK'); }
    public function giangVien() { return $this->belongsTo(GiangVien::class, 'MaTK', 'MaTK'); }
    public function monHoc() { return $this->belongsTo(MonHoc::class, 'MaMon', 'MaMon'); }
    public function lop() { return $this->belongsTo(Lop::class, 'MaLop', 'MaLop'); }
    public function hocKy() { return $this->belongsTo(HocKy::class, 'MaHocKy', 'MaHocKy'); }
    public function dangKyDeTais() { return $this->hasMany(DangKyDeTai::class, 'MaDeTai', 'MaDeTai'); }
}