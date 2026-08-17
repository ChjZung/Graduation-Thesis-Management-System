<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Khoa extends Model
{
    use HasFactory;

    protected $table = 'khoas';
    protected $primaryKey = 'MaKhoa';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaKhoa', 'TenKhoa'
    ];

    public $timestamps = true;
}
