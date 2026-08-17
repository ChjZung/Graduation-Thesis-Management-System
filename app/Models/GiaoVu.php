<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiaoVu extends Model
{
    use HasFactory;

    protected $table = 'giao_vus';
    protected $primaryKey = 'MaGVu';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaGVu', 'MaTK', 'MaKhoa', 'HoTen', 'Email', 'SoDienThoai', 'ChucVu'
    ];

    public $timestamps = true;
}
