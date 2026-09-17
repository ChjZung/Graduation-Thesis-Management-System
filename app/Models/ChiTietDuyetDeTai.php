<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChiTietDuyetDeTai extends Model
{
    use HasFactory;

    protected $table = 'ChiTietDuyetDeTai';
    protected $primaryKey = 'MaDuyet';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaDuyet',
        'NgayDuyet',
        'TrangThai',
        'LyDo',
        'MaGV',
        'MaDeTai',
    ];

    public $timestamps = true;

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'MaGV', 'MaGV');
    }

    public function deTai()
    {
        return $this->belongsTo(DeTai::class, 'MaDeTai', 'MaDeTai');
    }
}
