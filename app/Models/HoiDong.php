<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HoiDong extends Model
{
    use HasFactory;

    protected $table = 'hoi_dongs';
    protected $primaryKey = 'MaHoiDong';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaHoiDong', 'TenHoiDong', 'ThoiGianBatDau', 'ThoiGianKetThuc', 'DiaDiem', 'TrangThai', 'GhiChu'
    ];

    public $timestamps = true;
}
