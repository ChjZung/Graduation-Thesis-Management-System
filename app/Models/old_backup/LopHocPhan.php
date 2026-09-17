<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LopHocPhan extends Model
{
    protected $table = 'lop_hoc_phans';
    protected $primaryKey = 'MaLopHP';

    protected $fillable = [
        'TenLopHP',
        'MaMon',
        'MaHocKy',
        'MaGV',
        'SiSoToiDa',
        'TrangThai'
    ];

    public function monHoc()
    {
        return $this->belongsTo(MonHoc::class, 'MaMon', 'MaMon');
    }

    public function hocKy()
    {
        return $this->belongsTo(HocKy::class, 'MaHocKy', 'MaHocKy');
    }

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'MaGV', 'MaGV');
    }

    public function sinhVienLopHocPhans()
    {
        return $this->hasMany(SinhVienLopHocPhan::class, 'MaLopHP', 'MaLopHP');
    }

    public function sinhViens()
    {
        return $this->belongsToMany(SinhVien::class, 'sinh_vien_lop_hoc_phans', 'MaLopHP', 'MaSV');
    }
}
