<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChuyenNganh extends Model
{
    use HasFactory;

    protected $table = 'ChuyenNganh';
    protected $primaryKey = 'MaChuyenNganh';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaChuyenNganh',
        'TenChuyenNganh',
        'TrangThai',
        'MaNganh',
    ];

    public function nganh()
    {
        return $this->belongsTo(Nganh::class, 'MaNganh', 'MaNganh');
    }
}
