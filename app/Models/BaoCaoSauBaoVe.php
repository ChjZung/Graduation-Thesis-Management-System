<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaoCaoSauBaoVe extends Model
{
    use HasFactory;

    protected $table = 'bao_cao_sau_bao_ve';

    protected $fillable = [
        'MaHoSo',
        'LanChinhSua',
        'DuongDanFile',
        'TenFile',
        'DungLuong',
        'TomTatChinhSua',
        'NgayNop',
        'NguoiNop',
        'TrangThai', // 'Đã nộp', 'Đạt', 'Yêu cầu sửa tiếp'
        'NhanXetGV',
        'NgayKiemTra',
        'NguoiKiemTra',
    ];

    protected function casts(): array
    {
        return [
            'NgayNop' => 'datetime',
            'NgayKiemTra' => 'datetime',
            'LanChinhSua' => 'integer',
        ];
    }

    public function hoSoBaoVe()
    {
        return $this->belongsTo(HoSoBaoVe::class, 'MaHoSo', 'MaHoSo');
    }

    public function sinhVienNop()
    {
        return $this->belongsTo(SinhVien::class, 'NguoiNop', 'MaSV');
    }
}
