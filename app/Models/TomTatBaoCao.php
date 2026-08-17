<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TomTatBaoCao extends Model
{
    use HasFactory;

    protected $table = 'tom_tat_bao_caos';
    protected $primaryKey = 'MaTomTat';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaTomTat', 'MaBaoCao', 'CongViecDaHoanThanh', 'KhoKhan', 'KeHoachTuanToi', 'NoiDungAI', 'DoTinCayAI', 'NgayTomTat', 'TrangThai'
    ];

    public $timestamps = true;
}
