<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DanhSachSVDuDieuKien extends Model
{
    use HasFactory;

    protected $table = 'DanhSachSVDuDieuKien';
    protected $primaryKey = 'MaDSDK';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaDSDK',
        'NgayXetDuyet',
        'TrangThai',
        'GhiChu',
        'DieuKien',
        'MaSV',
        'MaHocKy',
    ];

    public $timestamps = true;

    public function sinhVien()
    {
        return $this->belongsTo(SinhVien::class, 'MaSV', 'MaSV');
    }

    public function hocKy()
    {
        return $this->belongsTo(HocKy::class, 'MaHocKy', 'MaHocKy');
    }
}
