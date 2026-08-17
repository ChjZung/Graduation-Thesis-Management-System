<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NhanXet extends Model
{
    use HasFactory;

    protected $table = 'nhan_xets';
    protected $primaryKey = 'MaNhanXet';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaNhanXet', 'MaBaoCao', 'MaGV', 'NoiDung', 'LoaiNhanXet', 'NgayNhanXet', 'TrangThai'
    ];

    public $timestamps = true;
}
