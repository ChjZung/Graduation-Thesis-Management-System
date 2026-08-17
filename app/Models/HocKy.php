<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HocKy extends Model
{
    use HasFactory;

    protected $table = 'hoc_kies';
    protected $primaryKey = 'MaHocKy';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaHocKy', 'TenHocKy', 'NamHoc', 'NgayBatDau', 'NgayKetThuc', 'TrangThai'
    ];

    public $timestamps = true;
}
