<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChiTieuHuongDan extends Model
{
    use HasFactory;

    protected $table = 'ChiTieuHuongDan';
    protected $primaryKey = 'MaChiTieu';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaChiTieu',
        'SoNhomToiDa',
        'NgayPhanBo',
        'MaHocKy',
        'MaGV',
    ];

    public $timestamps = true;

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'MaGV', 'MaGV');
    }

    public function hocKy()
    {
        return $this->belongsTo(HocKy::class, 'MaHocKy', 'MaHocKy');
    }
}
