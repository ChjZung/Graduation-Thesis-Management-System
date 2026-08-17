<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TienDo extends Model
{
    use HasFactory;

    protected $table = 'tien_dos';
    protected $primaryKey = 'MaTienDo';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaTienDo', 'MaNhom', 'MaMoc', 'TyLeHoanThanh', 'TrangThai', 'NgayCapNhat', 'GhiChu'
    ];

    public $timestamps = true;
}
