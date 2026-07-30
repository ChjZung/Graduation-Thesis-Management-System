<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lop extends Model
{
    protected $table = 'lops';
    protected $primaryKey = 'MaLop';
    protected $fillable = ['TenLop', 'MaNganh', 'KhoaHoc'];

    public function nganh()
    {
        return $this->belongsTo(Nganh::class, 'MaNganh', 'MaNganh');
    }
}
