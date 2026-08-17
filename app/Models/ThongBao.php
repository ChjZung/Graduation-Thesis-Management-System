<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThongBao extends Model
{
    use HasFactory;

    protected $table = 'thong_baos';
    protected $primaryKey = 'MaThongBao';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaThongBao', 'MaGVu', 'TieuDe', 'NoiDung', 'LoaiThongBao', 'DoiTuongNhan', 'NgayTao', 'TrangThai'
    ];

    public $timestamps = true;
}
