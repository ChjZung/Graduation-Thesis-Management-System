<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThanhVienNhom extends Model
{
    use HasFactory;

    protected $table = 'thanh_vien_nhoms';
    protected $primaryKey = 'MaNhom';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaNhom', 'MaSV', 'VaiTro', 'TrangThai', 'NgayThamGia'
    ];

    public $timestamps = true;

    /**
     * Hỗ trợ Composite Primary Key (MaNhom, MaSV) cho Eloquent update/save query
     */
    protected function setKeysForSaveQuery($query)
    {
        $query->where('MaNhom', '=', $this->getAttribute('MaNhom'))
              ->where('MaSV', '=', $this->getAttribute('MaSV'));

        return $query;
    }

    public function sinhVien()
    {
        return $this->belongsTo(SinhVien::class, 'MaSV', 'MaSV');
    }

    public function nhom()
    {
        return $this->belongsTo(Nhom::class, 'MaNhom', 'MaNhom');
    }
}
