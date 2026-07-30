<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class GiangVien extends Model
{
    protected $table = 'giang_viens';
    protected $primaryKey = 'MaGV';
    protected $fillable = ['MaTK', 'MaBoMon', 'HoTen', 'Email', 'SoDienThoai', 'HocVi'];
    
    public function taiKhoan() { return $this->belongsTo(TaiKhoan::class, 'MaTK', 'MaTK'); }
    public function boMon() { return $this->belongsTo(BoMon::class, 'MaBoMon', 'MaBoMon'); }
}