<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaiLieuNop extends Model
{
    use HasFactory;

    protected $table = 'TaiLieuNop';
    protected $primaryKey = 'MaTaiLieu';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaTaiLieu',
        'TenTaiLieu',
        'DuongDan',
        'LoaiTaiLieu',
        'LanNop',
        'NgayNop',
        'GhiChu',
        'MaDeTai',
    ];

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'NgayNop' => 'date',
            'LanNop' => 'integer',
        ];
    }

    public function deTai()
    {
        return $this->belongsTo(DeTai::class, 'MaDeTai', 'MaDeTai');
    }
}
