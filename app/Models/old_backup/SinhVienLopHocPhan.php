<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SinhVienLopHocPhan extends Model
{
    protected $table = 'sinh_vien_lop_hoc_phans';

    protected $fillable = [
        'MaSV',
        'MaLopHP',
        'MaMon',
        'MaHocKy',
        'NgayDangKy'
    ];

    public function sinhVien()
    {
        return $this->belongsTo(SinhVien::class, 'MaSV', 'MaSV');
    }

    public function lopHocPhan()
    {
        return $this->belongsTo(LopHocPhan::class, 'MaLopHP', 'MaLopHP');
    }

    public function monHoc()
    {
        return $this->belongsTo(MonHoc::class, 'MaMon', 'MaMon');
    }

    public function hocKy()
    {
        return $this->belongsTo(HocKy::class, 'MaHocKy', 'MaHocKy');
    }
}
