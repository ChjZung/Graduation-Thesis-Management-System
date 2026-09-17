<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PhanCongHuongDanLop extends Model
{
    protected $table = 'phan_cong_huong_dan_lops';
    protected $primaryKey = 'MaPhanCong';
    public $timestamps = false;
    
    protected $fillable = [
        'MaGV', 'MaLop', 'MaHocKy', 'NgayPhanCong'
    ];

    public function giangVien() { return $this->belongsTo(GiangVien::class, 'MaGV', 'MaGV'); }
    public function lop() { return $this->belongsTo(Lop::class, 'MaLop', 'MaLop'); }
    public function hocKy() { return $this->belongsTo(HocKy::class, 'MaHocKy', 'MaHocKy'); }
}
