<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LichGapHuongDan extends Model
{
    use HasFactory;

    protected $table = 'LichGapHuongDan';
    protected $primaryKey = 'MaLichGap';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaLichGap',
        'ThoiGianBatDau',
        'ThoiGianKetThuc',
        'DiaDiem',
        'HinhThuc',     // 'TrucTiep', 'TrucTuyen'
        'LinkCuocHop',
        'NoiDung',
        'SoNhomToiDa',
        'SoNhomDaDangKy',
        'TrangThai',    // 'MoDangKy', 'DaDuCho', 'DongDangKy', 'DaHuy', 'DaHoanThanh'
        'LyDoHuy',
        'NgayTao',
        'MaNhom',
        'MaGV',
    ];

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'ThoiGianBatDau' => 'datetime',
            'ThoiGianKetThuc' => 'datetime',
            'NgayTao' => 'date',
            'SoNhomToiDa' => 'integer',
            'SoNhomDaDangKy' => 'integer',
        ];
    }

    public function nhom()
    {
        return $this->belongsTo(Nhom::class, 'MaNhom', 'MaNhom');
    }

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'MaGV', 'MaGV');
    }

    public function dangKys()
    {
        return $this->hasMany(DangKyLichHuongDan::class, 'MaLichGap', 'MaLichGap');
    }

    public function dangKyHieuLucs()
    {
        return $this->hasMany(DangKyLichHuongDan::class, 'MaLichGap', 'MaLichGap')
            ->where('TrangThai', 'DaXacNhan');
    }

    /**
     * Số chỗ còn lại trong khung giờ
     */
    public function getSoChoConLaiAttribute(): int
    {
        $max = $this->SoNhomToiDa ?: 1;
        $booked = $this->SoNhomDaDangKy ?: 0;
        return max(0, $max - $booked);
    }

    /**
     * Trạng thái tính toán theo thực tế thời gian và số chỗ
     */
    public function getTrangThaiThucTeAttribute(): string
    {
        if ($this->TrangThai === 'DaHuy') {
            return 'DaHuy';
        }
        if ($this->TrangThai === 'DongDangKy') {
            return 'DongDangKy';
        }
        if ($this->ThoiGianBatDau < now()) {
            return 'DaQuaHan';
        }
        if ($this->so_cho_con_lai <= 0) {
            return 'DaDuCho';
        }
        return 'MoDangKy';
    }

    /**
     * Nhãn hiển thị thân thiện tiếng Việt
     */
    public function getTenTrangThaiAttribute(): string
    {
        return match ($this->trang_thai_thuc_te) {
            'MoDangKy'   => 'Mở đăng ký',
            'DaDuCho'    => 'Đã đủ chỗ',
            'DongDangKy' => 'Đã đóng đăng ký',
            'DaHuy'      => 'Đã hủy',
            'DaQuaHan'   => 'Đã qua hạn',
            default      => $this->TrangThai ?? 'Mở đăng ký',
        };
    }

    /**
     * Khung giờ có khả dụng cho sinh viên đăng ký hay không
     */
    public function getIsKhaDungAttribute(): bool
    {
        return ($this->trang_thai_thuc_te === 'MoDangKy') && ($this->so_cho_con_lai > 0);
    }
}
