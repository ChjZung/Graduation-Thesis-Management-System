<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lop extends Model
{
    use HasFactory;

    protected $table = 'lops';
    protected $primaryKey = 'MaLop';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaLop', 'TenLop', 'MaNganh', 'KhoaHoc'
    ];

    public $timestamps = true;

    public function nganh()
    {
        return $this->belongsTo(Nganh::class, 'MaNganh', 'MaNganh');
    }

    public function sinhViens()
    {
        return $this->hasMany(SinhVien::class, 'MaLop', 'MaLop');
    }
}
