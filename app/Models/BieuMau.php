<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BieuMau extends Model
{
    use HasFactory;

    protected $table = 'BieuMau';
    protected $primaryKey = 'MaBieuMau';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaBieuMau',
        'TenBieuMau',
        'DuongDanFile',
        'MakeHoach',
    ];

    public $timestamps = true;

    public function keHoachKhoaLuan()
    {
        return $this->belongsTo(KeHoachKhoaLuan::class, 'MakeHoach', 'MakeHoach');
    }
}
