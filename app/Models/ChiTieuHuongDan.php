<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChiTieuHuongDan extends Model
{
    use HasFactory;

    protected $table = 'chi_tieu_huong_dans';
    protected $primaryKey = 'MaGV';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaGV', 'MaHocKy', 'SoNhomToiDa', 'NgayPhanBo'
    ];

    public $timestamps = true;
}
