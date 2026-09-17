<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LichGapHuongDan extends Model
{
    use HasFactory;

    protected $table = 'LichGapHuongDan';
    protected $primaryKey = 'MaLichGap';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaLichGap',
        'ThoiGianBatDau',
        'ThoiGianKetThuc',
        'DiaDiem',
        'NoiDung',
        'TrangThai',
        'NgayTao',
        'MaNhom',
        'MaGV',
    ];

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'ThoiGianBatDau' => 'datetime',
            'ThoiGianKetThuc' => 'datetime',
            'NgayTao' => 'date',
        ];
    }

    public function nhom()
    {
        return $this->belongsTo(Nhom::class, 'MaNhom', 'MaNhom');
    }

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'MaGV', 'MaGV');
    }
}
