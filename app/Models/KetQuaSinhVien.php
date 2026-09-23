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

    public function getDiemHuongDanAttribute()
    {
        if (isset($this->attributes['DiemHuongDan'])) {
            return (float)$this->attributes['DiemHuongDan'];
        }
        if ($this->DiemTongKet && $this->DiemPhanBien && $this->DiemHoiDongTB) {
            $val = ($this->DiemTongKet - ($this->DiemPhanBien * 0.3) - ($this->DiemHoiDongTB * 0.4)) / 0.3;
            return round(max(0, min(10, $val)), 2);
        }
        return 0.0;
    }

    public function getDiemHe4Attribute()
    {
        $d = (float)$this->DiemTongKet;
        return match(true) {
            $d >= 8.5 => 4.0,
            $d >= 8.0 => 3.5,
            $d >= 7.0 => 3.0,
            $d >= 6.5 => 2.5,
            $d >= 5.5 => 2.0,
            $d >= 5.0 => 1.5,
            $d >= 4.0 => 1.0,
            default   => 0.0,
        };
    }

    public function getDiemChuAttribute()
    {
        $d = (float)$this->DiemTongKet;
        return match(true) {
            $d >= 8.5 => 'A',
            $d >= 8.0 => 'B+',
            $d >= 7.0 => 'B',
            $d >= 6.5 => 'C+',
            $d >= 5.5 => 'C',
            $d >= 5.0 => 'D+',
            $d >= 4.0 => 'D',
            default   => 'F',
        };
    }

    public static function xepLoai(float $diem): string
    {
        return match(true) {
            $diem >= 9.0 => 'Xuất sắc',
            $diem >= 8.0 => 'Giỏi',
            $diem >= 7.0 => 'Khá',
            $diem >= 5.5 => 'Trung bình',
            default      => 'Không đạt',
        };
    }
}
