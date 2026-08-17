<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChiTietDiemHoiDong extends Model
{
    use HasFactory;

    protected $table = 'chi_tiet_diem_hoi_dongs';
    protected $primaryKey = 'MaHoiDong';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaHoiDong', 'MaGV', 'MaSV', 'Diem', 'NhanXet', 'NgayCham'
    ];

    public $timestamps = true;
}
