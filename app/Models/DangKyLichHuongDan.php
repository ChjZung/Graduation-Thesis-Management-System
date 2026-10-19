<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DangKyLichHuongDan extends Model
{
    use HasFactory;

    protected $table = 'dang_ky_lich_huong_dan';

    protected $fillable = [
        'MaLichGap',
        'MaNhom',
        'MaSV',
        'NgayDangKy',
        'GhiChu',
        'TrangThai', // 'DaXacNhan', 'DaHuy', 'DaHoanThanh'
        'LyDoHuy',
        'NguoiHuy', // 'SinhVien', 'GiangVien'
        'ThoiGianHuy',
        'KetQuaGap',
    ];

    protected function casts(): array
    {
        return [
            'NgayDangKy'  => 'datetime',
            'ThoiGianHuy' => 'datetime',
        ];
    }

    public function lichGap()
    {
        return $this->belongsTo(LichGapHuongDan::class, 'MaLichGap', 'MaLichGap');
    }

    public function nhom()
    {
        return $this->belongsTo(Nhom::class, 'MaNhom', 'MaNhom');
    }

    public function sinhVien()
    {
        return $this->belongsTo(SinhVien::class, 'MaSV', 'MaSV');
    }

    /**
     * Tự động loại bỏ thẻ HTML (nếu có) để hiển thị thuần văn bản sạch
     */
    public function getGhiChuAttribute($value): ?string
    {
        return $value !== null ? trim(strip_tags($value)) : null;
    }

    public function getLyDoHuyAttribute($value): ?string
    {
        return $value !== null ? trim(strip_tags($value)) : null;
    }
}
