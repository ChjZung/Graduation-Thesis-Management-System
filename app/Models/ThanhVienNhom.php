<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThanhVienNhom extends Model
{
    use HasFactory;

    protected $table = 'ThanhVienNhom';
    public $incrementing = false;
    protected $primaryKey = ['MaNhom', 'MaSV'];

    protected $fillable = [
        'MaNhom',
        'MaSV',
        'VaiTro',
        'TrangThai',
        'NgayThamGia',
    ];

    public $timestamps = true;

    protected function setKeysForSaveQuery($query)
    {
        return $query->where('MaNhom', $this->getAttribute('MaNhom'))
                     ->where('MaSV', $this->getAttribute('MaSV'));
    }

    public function nhom()
    {
        return $this->belongsTo(Nhom::class, 'MaNhom', 'MaNhom');
    }

    public function sinhVien()
    {
        return $this->belongsTo(SinhVien::class, 'MaSV', 'MaSV');
    }
}
