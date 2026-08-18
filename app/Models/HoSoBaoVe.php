<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HoSoBaoVe extends Model
{
    use HasFactory;

    protected $table = 'ho_so_bao_ves';
    protected $primaryKey = 'MaHoSo';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaHoSo', 'MaNhom', 'MaHoiDong', 'MaGVPhanBien',
        'XacNhanGVHD', 'NgayXacNhanGVHD', 'TyLeTrungLap',
        'MinhChungDaoVan', 'TrangThai', 'NgayNop', 'NgayXacNhan',
        'NguoiXacNhan', 'GhiChu',
    ];

    protected $casts = [
        'XacNhanGVHD' => 'boolean',
    ];

    public $timestamps = true;

    public function nhom()
    {
        return $this->belongsTo(Nhom::class, 'MaNhom', 'MaNhom');
    }

    public function hoiDong()
    {
        return $this->belongsTo(HoiDong::class, 'MaHoiDong', 'MaHoiDong');
    }

    public function giangVienPhanBien()
    {
        return $this->belongsTo(GiangVien::class, 'MaGVPhanBien', 'MaGV');
    }
}

