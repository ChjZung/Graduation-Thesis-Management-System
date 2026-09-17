<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HoiDong extends Model
{
    use HasFactory;

    protected $table = 'HoiDong';
    protected $primaryKey = 'MaHoiDong';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaHoiDong',
        'TenHoiDong',
        'ThoiGianBatDau',
        'ThoiGianKetThuc',
        'DiaDiem',
        'TrangThai',
        'GhiChu',
        'NgayBaoVe',
        'MaDeTai',
        'MaGV',
        'MaHocKy',
    ];

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'ThoiGianBatDau' => 'datetime',
            'ThoiGianKetThuc' => 'datetime',
            'NgayBaoVe' => 'date',
        ];
    }

    public function deTai()
    {
        return $this->belongsTo(DeTai::class, 'MaDeTai', 'MaDeTai');
    }

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'MaGV', 'MaGV');
    }

    public function hocKy()
    {
        return $this->belongsTo(HocKy::class, 'MaHocKy', 'MaHocKy');
    }

    public function thanhVienHoiDongs()
    {
        return $this->hasMany(ThanhVienHoiDong::class, 'MaHoiDong', 'MaHoiDong');
    }

    public function thanhViens()
    {
        return $this->thanhVienHoiDongs();
    }

    public function giangViens()
    {
        return $this->belongsToMany(GiangVien::class, 'ThanhVienHoiDong', 'MaHoiDong', 'MaGV')
                    ->withPivot('VaiTro')
                    ->withTimestamps();
    }

    public function hoSoBaoVes()
    {
        return $this->hasMany(HoSoBaoVe::class, 'MaHoiDong', 'MaHoiDong');
    }

    public function phieuChamDiems()
    {
        return $this->hasMany(PhieuChamDiem::class, 'MaHoiDong', 'MaHoiDong');
    }
}
