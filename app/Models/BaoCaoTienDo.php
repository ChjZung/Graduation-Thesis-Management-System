<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BaoCaoTienDo extends Model
{
    use HasFactory;

    protected $table = 'bao_cao_tien_dos';
    protected $primaryKey = 'MaBaoCao';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaBaoCao', 'MaNhom', 'LanBaoCao', 'TieuDe',
        'NoiDungBaoCao', 'NgayNop', 'TenFile', 'DuongDanFile',
        'LinkCode', 'TrangThai',
    ];

    public $timestamps = true;

    public function nhom()
    {
        return $this->belongsTo(Nhom::class, 'MaNhom', 'MaNhom');
    }

    public function tomTat()
    {
        return $this->hasOne(TomTatBaoCao::class, 'MaBaoCao', 'MaBaoCao');
    }

    public function nhanXets()
    {
        return $this->hasMany(NhanXet::class, 'MaBaoCao', 'MaBaoCao');
    }
}
