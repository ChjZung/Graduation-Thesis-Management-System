<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TomTatBaoCao extends Model
{
    use HasFactory;

    protected $table = 'tom_tat_bao_caos';
    protected $primaryKey = 'MaTomTat';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaTomTat', 'MaBaoCao', 'CongViecDaHoanThanh',
        'KhoKhan', 'KeHoachTuanToi', 'NoiDungAI',
        'DoTinCayAI', 'NgayTomTat', 'TrangThai',
    ];

    protected $casts = [
        'NgayTomTat' => 'datetime',
        'DoTinCayAI' => 'float',
    ];

    public $timestamps = true;

    public function baoCao()
    {
        return $this->belongsTo(BaoCaoTienDo::class, 'MaBaoCao', 'MaBaoCao');
    }
}
