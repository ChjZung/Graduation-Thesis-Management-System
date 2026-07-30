<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SanPham extends Model
{
    protected $table = 'san_phams';
    protected $primaryKey = 'MaSanPham';
    public $timestamps = false;

    protected $fillable = [
        'MaNhom',
        'TenSanPham',
        'LinkFile',
        'NgayNop'
    ];

    public function nhomDoAn()
    {
        return $this->belongsTo(NhomDoAn::class, 'MaNhom', 'MaNhom');
    }
}
