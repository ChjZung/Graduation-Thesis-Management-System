<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HuongDan extends Model
{
    protected $table = 'huong_dans';
    protected $primaryKey = 'MaHuongDan';
    public $timestamps = false;

    protected $fillable = [
        'MaNhom',
        'MaGV',
        'MaDeTai',
        'NgayPhanCong',
        'TrangThai'
    ];

    public function nhomDoAn()
    {
        return $this->belongsTo(NhomDoAn::class, 'MaNhom', 'MaNhom');
    }

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'MaGV', 'MaGV');
    }

    public function deTai()
    {
        return $this->belongsTo(DeTai::class, 'MaDeTai', 'MaDeTai');
    }
}
