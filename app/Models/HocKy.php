<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HocKy extends Model
{
    protected $table = 'hoc_kies';
    protected $primaryKey = 'MaHocKy';
    protected $fillable = ['TenHocKy', 'NamHoc', 'NgayBatDau', 'NgayKetThuc'];

    public function lopHocPhans()
    {
        return $this->hasMany(LopHocPhan::class, 'MaHocKy', 'MaHocKy');
    }
}
