<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChiTietDiemHoiDong extends Model
{
    use HasFactory;

    protected $table = 'chi_tiet_diem_hoi_dongs';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'MaHoiDong', 'MaGV', 'MaSV', 'Diem', 'NhanXet', 'NgayCham',
    ];

    protected $casts = [
        'Diem' => 'float',
        'NgayCham' => 'datetime',
    ];

    public $timestamps = true;

    public function hoiDong()
    {
        return $this->belongsTo(HoiDong::class, 'MaHoiDong', 'MaHoiDong');
    }

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'MaGV', 'MaGV');
    }

    public function sinhVien()
    {
        return $this->belongsTo(SinhVien::class, 'MaSV', 'MaSV');
    }
}
