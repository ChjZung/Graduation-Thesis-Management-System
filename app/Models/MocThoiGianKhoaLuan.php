<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MocThoiGianKhoaLuan extends Model
{
    use HasFactory;

    protected $table = 'moc_thoi_gian_khoa_luans';
    protected $primaryKey = 'MaMoc';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaMoc', 'MaKeHoach', 'LoaiGiaiDoan', 'ThuTu', 'TenMoc', 'DoiTuongThucHien',
        'NgayBatDau', 'NgayKetThuc', 'MoTa', 'BatBuoc'
    ];

    protected $casts = [
        'BatBuoc' => 'boolean',
    ];

    public $timestamps = true;

    public function keHoach()
    {
        return $this->belongsTo(KeHoachKhoaLuan::class, 'MaKeHoach', 'MaKeHoach');
    }
}
