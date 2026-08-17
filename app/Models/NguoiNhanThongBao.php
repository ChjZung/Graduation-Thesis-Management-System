<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NguoiNhanThongBao extends Model
{
    use HasFactory;

    protected $table = 'nguoi_nhan_thong_baos';
    protected $primaryKey = 'MaThongBao';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaThongBao', 'MaTK', 'DaDoc', 'NgayDoc'
    ];

    public $timestamps = true;
}
