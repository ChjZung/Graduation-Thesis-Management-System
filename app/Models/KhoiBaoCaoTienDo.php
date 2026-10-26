<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KhoiBaoCaoTienDo extends Model
{
    use HasFactory;

    protected $table = 'khoibaocaotiendo';
    protected $primaryKey = 'MaKhoi';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaKhoi',
        'MaBaoCao',
        'MaDotNop',
        'MaGiaiDoan',
        'MaDeTai',
        'LanBaoCao',
        'LanNop',
        'TieuDe',
        'NoiDungBaoCao',
        'KetQuaDatDuoc',
        'KhoKhan',
        'CongViecChuaHoanThanh',
        'HuongGiaiQuyet',
        'KeHoachTiepTheo',
        'PhanTramHoanThanh',
        'TenFile',
        'DuongDanFile',
        'LinkCode',
        'TrangThai',
        'NhanXetGV',
        'NguoiNhanXet',
        'NgayNop',
        'NgayNhanXet',
        'TomTatAI',
        'TomTatJson',
    ];

    protected function casts(): array
    {
        return [
            'NgayNop' => 'datetime',
            'NgayNhanXet' => 'datetime',
            'LanBaoCao' => 'integer',
            'LanNop' => 'integer',
            'PhanTramHoanThanh' => 'integer',
            'TomTatJson' => 'array',
        ];
    }

    public function dotNop()
    {
        return $this->belongsTo(DotNopBaoCao::class, 'MaDotNop', 'MaDotNop');
    }

    public function giaiDoan()
    {
        return $this->belongsTo(GiaiDoanThucHien::class, 'MaGiaiDoan', 'MaGiaiDoan');
    }

    public function baoCao()
    {
        return $this->belongsTo(BaoCaoTienDo::class, 'MaBaoCao', 'MaBaoCao');
    }

    public function baoCaoTienDo()
    {
        return $this->baoCao();
    }

    public function deTai()
    {
        return $this->belongsTo(DeTai::class, 'MaDeTai', 'MaDeTai');
    }

    public function tomTat()
    {
        return $this->hasOne(TomTatBaoCao::class, 'MaKhoi', 'MaKhoi');
    }

    public function getLinkCodeAttribute()
    {
        if (!empty($this->attributes['LinkCode'])) {
            return $this->attributes['LinkCode'];
        }
        if (preg_match('/Link Code:\s*(https?:\/\/[^\s\n\r]+)/i', $this->NoiDungBaoCao ?? '', $matches)) {
            return trim($matches[1]);
        }
        if (preg_match('/(https?:\/\/(github|gitlab)\.com\/[^\s\n\r]+)/i', $this->NoiDungBaoCao ?? '', $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    public function getTyLeHoanThanhHienThi(): array
    {
        if ($this->PhanTramHoanThanh === null) {
            return [
                'value' => null,
                'label' => 'Chưa đánh giá',
                'badge' => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                'is_evaluated' => false,
            ];
        }
        return [
            'value' => (int)$this->PhanTramHoanThanh,
            'label' => $this->PhanTramHoanThanh . '%',
            'badge' => $this->PhanTramHoanThanh >= 80 ? 'bg-success text-white' : ($this->PhanTramHoanThanh >= 50 ? 'bg-primary text-white' : 'bg-warning text-dark'),
            'is_evaluated' => true,
        ];
    }
}
