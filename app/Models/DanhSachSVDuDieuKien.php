<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DanhSachSVDuDieuKien extends Model
{
    use HasFactory;

    protected $table = 'danh_sach_sv_du_dieu_kiens';
    protected $primaryKey = 'MaSV';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaSV', 'MaHocKy', 'NgayXetDuyet', 'TrangThai', 'GhiChu'
    ];

    public $timestamps = true;
}
