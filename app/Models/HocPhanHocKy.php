<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HocPhanHocKy extends Model
{
    use HasFactory;

    protected $table = 'HocPhan_HocKy';

    protected $fillable = [
        'MaHocPhan',
        'MaHocKy',
        'TrangThai',
        'GhiChu',
    ];

    public $timestamps = true;

    public function hocPhan()
    {
        return $this->belongsTo(HocPhan::class, 'MaHocPhan', 'MaHocPhan');
    }

    public function hocKy()
    {
        return $this->belongsTo(HocKy::class, 'MaHocKy', 'MaHocKy');
    }
}
