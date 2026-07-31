<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SinhVien extends Model
{
    protected $table = 'sinh_viens';
    protected $primaryKey = 'MaSV';
    protected $fillable = ['MaTK', 'MaLop', 'HoTen', 'Email', 'SoDienThoai'];
    
    public function taiKhoan() { return $this->belongsTo(TaiKhoan::class, 'MaTK', 'MaTK'); }
    public function lop() { return $this->belongsTo(Lop::class, 'MaLop', 'MaLop'); }
    public function thanhVienNhom() { return $this->hasOne(ThanhVienNhom::class, 'MaSV', 'MaSV'); }
    public function thanhVienNhoms() { return $this->hasMany(ThanhVienNhom::class, 'MaSV', 'MaSV'); }
    public function lopHocPhans() { return $this->belongsToMany(LopHocPhan::class, 'sinh_vien_lop_hoc_phans', 'MaSV', 'MaLopHP'); }
}