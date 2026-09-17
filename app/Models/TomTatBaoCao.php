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
        'CongViecDaHoanThanh',
        'KhoKhan',
        'KeHoachTuanToi',
        'NoiDungAI',
        'TrangThai',
        'NgayTomTat',
        'MaBaoCao',
    ];

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'NgayTomTat' => 'date',
        ];
    }

    public function baoCaoTienDo()
    {
        return $this->belongsTo(BaoCaoTienDo::class, 'MaBaoCao', 'MaBaoCao');
    }

    public function getDoTinCayAIAttribute()
    {
        return $this->attributes['DoTinCayAI'] ?? 95.00;
    }

    public function setDoTinCayAIAttribute($value)
    {
        // No-op or keep in attributes memory
        $this->attributes['DoTinCayAI'] = $value;
    }
}
