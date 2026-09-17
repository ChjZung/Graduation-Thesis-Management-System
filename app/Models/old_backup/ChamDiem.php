<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChamDiem extends Model
{
    protected $table = 'cham_diems';
    protected $primaryKey = 'MaCham';
    // DB table HAS created_at/updated_at — timestamps enabled
    public $timestamps = true;

    protected $fillable = [
        'MaNhom',
        'MaGV',
        'LoaiCham',
        'DiemBaoCao',
        'DiemBaoVe',
        'DiemTong',
        'NhanXet',
        'NgayCham'
    ];

    protected $casts = [
        'DiemBaoCao' => 'float',
        'DiemBaoVe'  => 'float',
        'DiemTong'   => 'float',
    ];

    public function nhomDoAn()
    {
        return $this->belongsTo(NhomDoAn::class, 'MaNhom', 'MaNhom');
    }

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'MaGV', 'MaGV');
    }
}
