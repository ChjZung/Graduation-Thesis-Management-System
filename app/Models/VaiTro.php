<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VaiTro extends Model
{
    protected $table = 'vai_tros';
    protected $primaryKey = 'MaVaiTro';

    protected $fillable = [
        'TenVaiTro',
    ];

    public function taiKhoans()
    {
        return $this->hasMany(TaiKhoan::class, 'MaVaiTro', 'MaVaiTro');
    }
}
