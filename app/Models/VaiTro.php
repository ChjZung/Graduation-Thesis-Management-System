<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaiTro extends Model
{
    use HasFactory;

    protected $table = 'VaiTro';
    protected $primaryKey = 'MaVaiTro';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaVaiTro',
        'TenVaiTro',
    ];

    public function taiKhoans()
    {
        return $this->hasMany(TaiKhoan::class, 'MaVaiTro', 'MaVaiTro');
    }
}
