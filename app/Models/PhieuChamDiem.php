<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhieuChamDiem extends Model
{
    use HasFactory;

    protected $table = 'PhieuChamDiem';
    protected $primaryKey = 'MaPhieuChamDiem';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaPhieuChamDiem',
        'Diem',
        'NgayCham',
        'NhanXet',
        'MaDeTai',
        'MaHoiDong',
        'MaGV',
        'MaHoSo',
        'MaHocKy',
    ];

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'Diem' => 'float',
            'NgayCham' => 'date',
        ];
    }

    public function deTai()
    {
        return $this->belongsTo(DeTai::class, 'MaDeTai', 'MaDeTai');
    }

    public function hoiDong()
    {
        return $this->belongsTo(HoiDong::class, 'MaHoiDong', 'MaHoiDong');
    }

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'MaGV', 'MaGV');
    }

    public function hoSoBaoVe()
    {
        return $this->belongsTo(HoSoBaoVe::class, 'MaHoSo', 'MaHoSo');
    }

    public function hocKy()
    {
        return $this->belongsTo(HocKy::class, 'MaHocKy', 'MaHocKy');
    }
}
