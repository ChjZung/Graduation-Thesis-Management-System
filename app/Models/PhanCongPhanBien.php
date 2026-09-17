<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhanCongPhanBien extends Model
{
    use HasFactory;

    protected $table = 'PhanCongPhanBien';
    protected $primaryKey = 'MaPhanCong';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaPhanCong',
        'VaiTro',
        'NgayPhanCong',
        'TrangThai',
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
