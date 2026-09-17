<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThanhVienHoiDong extends Model
{
    use HasFactory;

    protected $table = 'ThanhVienHoiDong';
    public $incrementing = false;
    protected $primaryKey = ['MaHoiDong', 'MaGV'];

    protected $fillable = [
        'MaHoiDong',
        'MaGV',
        'VaiTro',
    ];

    public $timestamps = true;

    protected function setKeysForSaveQuery($query)
    {
        return $query->where('MaHoiDong', $this->getAttribute('MaHoiDong'))
                     ->where('MaGV', $this->getAttribute('MaGV'));
    }

    public function hoiDong()
    {
        return $this->belongsTo(HoiDong::class, 'MaHoiDong', 'MaHoiDong');
    }

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'MaGV', 'MaGV');
    }
}
