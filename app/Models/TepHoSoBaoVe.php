<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TepHoSoBaoVe extends Model
{
    use HasFactory;

    protected $table = 'TepHoSoBaoVe';
    protected $primaryKey = 'MaTep';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaTep',
        'TenTep',
        'LoaiTep',
        'DuongDanFile',
        'PhienBan',
        'NgayNop',
        'TrangThai',
        'GhiChu',
        'MaHoSo',
    ];

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'NgayNop' => 'date',
        ];
    }

    public function hoSoBaoVe()
    {
        return $this->belongsTo(HoSoBaoVe::class, 'MaHoSo', 'MaHoSo');
    }
}
