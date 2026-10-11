<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChiTieuPhanBien extends Model
{
    use HasFactory;

    protected $table = 'chitieuphanbien';
    protected $primaryKey = 'id';

    protected $fillable = [
        'MaChiTieuPB',
        'MaGV',
        'MaHocKy',
        'SoDeTaiToiDa',
        'GhiChu',
    ];

    protected function casts(): array
    {
        return [
            'SoDeTaiToiDa' => 'integer',
        ];
    }

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'MaGV', 'MaGV');
    }

    public function hocKy()
    {
        return $this->belongsTo(HocKy::class, 'MaHocKy', 'MaHocKy');
    }
}
