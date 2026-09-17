<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KetQuaSinhVien extends Model
{
    use HasFactory;

    protected $table = 'KetQuaSinhVien';
    protected $primaryKey = 'MaKetQua';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaKetQua',
        'DiemPhanBien',
        'DiemHoiDongTB',
        'DiemTongKet',
        'KetQua',
        'NhanXetChung',
        'NgayCham',
        'MaSV',
        'MaHoSo',
        'MaHocKy',
    ];

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'DiemPhanBien' => 'float',
            'DiemHoiDongTB' => 'float',
            'DiemTongKet' => 'float',
            'NgayCham' => 'date',
        ];
    }

    public function sinhVien()
    {
        return $this->belongsTo(SinhVien::class, 'MaSV', 'MaSV');
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
