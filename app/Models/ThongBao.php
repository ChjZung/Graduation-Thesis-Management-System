<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThongBao extends Model
{
    protected $table = 'thong_baos';
    protected $primaryKey = 'MaThongBao';
    public $timestamps = false;

    protected $fillable = [
        'MaTK',
        'MaLop',
        'MaLopHP',
        'TieuDe',
        'NoiDung',
        'LoaiThongBao',
        'DuongDan',
        'DaDoc',
        'NgayTao'
    ];

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'MaTK', 'MaTK');
    }

    public function lop()
    {
        return $this->belongsTo(Lop::class, 'MaLop', 'MaLop');
    }

    public function lopHocPhan()
    {
        return $this->belongsTo(LopHocPhan::class, 'MaLopHP', 'MaLopHP');
    }

    public function scopeChuaDoc($query)
    {
        return $query->where('DaDoc', false);
    }
}
