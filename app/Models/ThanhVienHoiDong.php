<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThanhVienHoiDong extends Model
{
    use HasFactory;

    protected $table = 'thanh_vien_hoi_dongs';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaHoiDong', 'MaGV', 'VaiTro',
    ];

    public $timestamps = true;

    public function hoiDong()
    {
        return $this->belongsTo(HoiDong::class, 'MaHoiDong', 'MaHoiDong');
    }

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'MaGV', 'MaGV');
    }
}
