<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThanhVienNhom extends Model
{
    use HasFactory;

    protected $table = 'thanh_vien_nhoms';
    protected $primaryKey = 'MaNhom';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaNhom', 'MaSV', 'VaiTro', 'NgayThamGia'
    ];

    public $timestamps = true;
}
