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

    public function scopeChuaDoc($query)
    {
        return $query->where('DaDoc', false);
    }
}
