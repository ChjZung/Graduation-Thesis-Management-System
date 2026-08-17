<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nhom extends Model
{
    use HasFactory;

    protected $table = 'nhoms';
    protected $primaryKey = 'MaNhom';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaNhom', 'MaDeTai', 'MaTruongNhom', 'TenNhom', 'TrangThai', 'NgayTao'
    ];

    public $timestamps = true;
}
