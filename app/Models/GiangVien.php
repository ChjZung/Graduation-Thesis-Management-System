<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiangVien extends Model
{
    use HasFactory;

    protected $table = 'giang_viens';
    protected $primaryKey = 'MaGV';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaGV', 'MaTK', 'MaBoMon', 'MaSoCanBo', 'HoTen', 'NgaySinh', 'GioiTinh', 'Email', 'SoDienThoai', 'HocHam', 'HocVi', 'ChuyenNganh', 'TrangThai'
    ];

    public $timestamps = true;

    public function boMon()
    {
        return $this->belongsTo(BoMon::class, 'MaBoMon', 'MaBoMon');
    }
}

