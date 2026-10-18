<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LichSuChuyenGiaiDoan extends Model
{
    use HasFactory;

    protected $table = 'lichsuchuyengiaidoan';

    protected $fillable = [
        'MaNhom',
        'GiaiDoanTruoc',
        'GiaiDoanSau',
        'TrangThaiTruoc',
        'TrangThaiSau',
        'HanhDong',
        'NguoiThucHien',
        'LyDo',
    ];

    protected function casts(): array
    {
        return [
            'GiaiDoanTruoc' => 'integer',
            'GiaiDoanSau' => 'integer',
        ];
    }

    public function nhom()
    {
        return $this->belongsTo(Nhom::class, 'MaNhom', 'MaNhom');
    }
}
