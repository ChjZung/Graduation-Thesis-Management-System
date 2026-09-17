<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaoCaoTienDo extends Model
{
    use HasFactory;

    protected $table = 'BaoCaoTienDo';
    protected $primaryKey = 'MaBaoCao';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaBaoCao',
        'LanBaoCao',
        'TieuDe',
        'NoiDungBaoCao',
        'NgayNop',
        'TrangThai',
        'TenFile',
        'DuongDanFile',
        'NhanXet',
        'MaMoc',
        'MaDeTai',
    ];

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'NgayNop' => 'date',
        ];
    }

    public function mocThoiGian()
    {
        return $this->belongsTo(MocThoiGianKhoaLuan::class, 'MaMoc', 'MaMoc');
    }

    public function deTai()
    {
        return $this->belongsTo(DeTai::class, 'MaDeTai', 'MaDeTai');
    }

    public function tomTatBaoCao()
    {
        return $this->hasOne(TomTatBaoCao::class, 'MaBaoCao', 'MaBaoCao');
    }

    public function tomTat()
    {
        return $this->tomTatBaoCao();
    }

    public function nhom()
    {
        return $this->hasOneThrough(Nhom::class, PhieuDangKy::class, 'MaDeTai', 'MaNhom', 'MaDeTai', 'MaNhom');
    }

    public function getLinkCodeAttribute()
    {
        if (preg_match('/Link Code:\s*(https?:\/\/[^\s\n\r]+)/i', $this->NoiDungBaoCao ?? '', $matches)) {
            return trim($matches[1]);
        }
        if (preg_match('/(https?:\/\/(github|gitlab)\.com\/[^\s\n\r]+)/i', $this->NoiDungBaoCao ?? '', $matches)) {
            return trim($matches[1]);
        }
        return null;
    }
}
