<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaoCaoTienDo extends Model
{
    use HasFactory;

    protected $table = 'bao_cao_tien_dos';
    protected $primaryKey = 'MaBaoCao';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaBaoCao', 'MaNhom', 'LanBaoCao', 'TieuDe', 'NoiDungBaoCao', 'NgayNop', 'TenFile', 'DuongDanFile', 'TrangThai'
    ];

    public $timestamps = true;
}
