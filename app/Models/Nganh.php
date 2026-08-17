<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nganh extends Model
{
    use HasFactory;

    protected $table = 'nganhs';
    protected $primaryKey = 'MaNganh';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaNganh', 'TenNganh', 'MaKhoa'
    ];

    public $timestamps = true;
}
