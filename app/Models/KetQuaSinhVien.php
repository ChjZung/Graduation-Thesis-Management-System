<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KetQuaSinhVien extends Model
{
    use HasFactory;

    protected $table = 'ket_qua_sinh_viens';
    protected $primaryKey = 'MaKetQua';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaKetQua', 'MaSV', 'MaHocKy', 'DiemHuongDan', 'DiemPhanBien',
        'DiemHoiDongTB', 'DiemTongKet', 'KetQua', 'NhanXetChung', 'NgayCham',
    ];

    protected $casts = [
        'DiemHuongDan'  => 'float',
        'DiemPhanBiel'  => 'float',
        'DiemHoiDongTB' => 'float',
        'DiemTongKet'   => 'float',
        'NgayCham'      => 'date',
    ];

    public $timestamps = true;

    public function sinhVien()
    {
        return $this->belongsTo(SinhVien::class, 'MaSV', 'MaSV');
    }

    public function hocKy()
    {
        return $this->belongsTo(HocKy::class, 'MaHocKy', 'MaHocKy');
    }

    /**
     * Tự động xếp loại dựa theo điểm tổng kết
     */
    public static function xepLoai(float $diem): string
    {
        if ($diem >= 9.0)  return 'Xuất sắc';
        if ($diem >= 8.0)  return 'Giỏi';
        if ($diem >= 7.0)  return 'Khá';
        if ($diem >= 6.0)  return 'Trung bình';
        if ($diem >= 5.0)  return 'Trung bình yếu';
        return 'Không đạt';
    }

    /**
     * Tính điểm tổng kết: GVHD 30% + Phản biện 30% + HĐ 40%
     */
    public static function tinhDiemTongKet(float $gvhd, float $pb, float $hd): float
    {
        return round($gvhd * 0.3 + $pb * 0.3 + $hd * 0.4, 2);
    }
}
