<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TomTatBaoCao extends Model
{
    use HasFactory;

    protected $table = 'TomTatBaoCao';
    protected $primaryKey = 'MaTomTat';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaTomTat',
        'MaBaoCao',
        'MaKhoi',
        'TenFile',
        'DuongDanFile',
        'SoTrang',
        'CongViecDaHoanThanh',
        'KhoKhan',
        'KeHoachTuanToi',
        'NoiDungAI',
        'TomTatJson',
        'TyLeHoanThanhAI',
        'CanCuDanhGia',
        'ThoiDiemDanhGia',
        'TrangThaiDanhGia',
        'TrangThai',
        'TrangThaiXuLy',
        'ThoiGianXuLy',
        'LoiXuLy',
        'NgayTomTat',
    ];

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'NgayTomTat' => 'date',
            'ThoiDiemDanhGia' => 'datetime',
            'CanCuDanhGia' => 'array',
            'TomTatJson' => 'array',
            'ThoiGianXuLy' => 'float',
            'SoTrang' => 'integer',
            'TyLeHoanThanhAI' => 'integer',
        ];
    }

    public function baoCaoTienDo()
    {
        return $this->belongsTo(BaoCaoTienDo::class, 'MaBaoCao', 'MaBaoCao');
    }

    public function khoiBaoCao()
    {
        return $this->belongsTo(KhoiBaoCaoTienDo::class, 'MaKhoi', 'MaKhoi');
    }

    public function getDoTinCayAIAttribute()
    {
        return $this->attributes['DoTinCayAI'] ?? 95.00;
    }

    public function setDoTinCayAIAttribute($value)
    {
        $this->attributes['DoTinCayAI'] = $value;
    }
}
