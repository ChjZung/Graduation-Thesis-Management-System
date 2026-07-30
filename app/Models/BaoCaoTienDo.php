<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaoCaoTienDo extends Model
{
    protected $table = 'bao_cao_tien_dos';
    protected $primaryKey = 'MaBaoCao';
    public $timestamps = false;

    protected $fillable = [
        'MaNhom',
        'LanBaoCao',
        'NoiDung',
        'FileBaoCao',
        'TrangThai',
        'NgayNop'
    ];

    public function nhomDoAn()
    {
        return $this->belongsTo(NhomDoAn::class, 'MaNhom', 'MaNhom');
    }

    public function nhanXets()
    {
        return $this->hasMany(NhanXet::class, 'MaBaoCao', 'MaBaoCao');
    }
}
