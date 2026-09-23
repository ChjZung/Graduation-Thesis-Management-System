<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MocThoiGianKhoaLuan extends Model
{
    use HasFactory;

    protected $table = 'MocThoiGianKhoaLuan';
    protected $primaryKey = 'MaMoc';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaMoc',
        'TenMoc',
        'NgayBatDau',
        'NgayKetThuc',
        'MoTa',
        'MakeHoach',
    ];

    public $timestamps = true;

    public function keHoachKhoaLuan()
    {
        return $this->belongsTo(KeHoachKhoaLuan::class, 'MakeHoach', 'MakeHoach');
    }

    public function keHoach()
    {
        return $this->keHoachKhoaLuan();
    }

    public function baoCaoTienDos()
    {
        return $this->hasMany(BaoCaoTienDo::class, 'MaMoc', 'MaMoc');
    }
}
