<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BieuMau extends Model
{
    use HasFactory;

    protected $table = 'bieu_maus';
    protected $primaryKey = 'MaBieuMau';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaBieuMau', 'MaKeHoach', 'TenBieuMau', 'DuongDanFile'
    ];

    public $timestamps = true;
}
