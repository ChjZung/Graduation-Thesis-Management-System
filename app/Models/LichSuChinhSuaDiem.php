<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LichSuChinhSuaDiem extends Model
{
    use HasFactory;

    protected $table = 'lich_su_chinh_sua_diem';

    protected $fillable = [
        'MaPhieuChamDiem',
        'MaHoiDong',
        'MaHoSo',
        'MaSV',
        'MaGV',
        'NguoiThucHien',
        'MaCLO',
        'TenTieuChi',
        'DiemCu',
        'DiemMoi',
        'DiemTongCu',
        'DiemTongMoi',
        'ChiTietThayDoi',
        'LyDo',
        'ThoiGian',
    ];

    protected $casts = [
        'ChiTietThayDoi' => 'array',
        'ThoiGian'       => 'datetime',
        'DiemCu'         => 'float',
        'DiemMoi'        => 'float',
        'DiemTongCu'     => 'float',
        'DiemTongMoi'    => 'float',
    ];

    /**
     * Quy tắc bảo mật kiểm toán: Tuyệt đối không cho phép xóa bản ghi lịch sử chỉnh sửa điểm
     */
    protected static function booted()
    {
        static::deleting(function ($model) {
            throw new \RuntimeException('Không được phép xóa lịch sử chỉnh sửa điểm theo quy chế bảo toàn dữ liệu kiểm toán (Immutable Audit Log).');
        });
    }

    public function phieuChamDiem()
    {
        return $this->belongsTo(PhieuChamDiem::class, 'MaPhieuChamDiem', 'MaPhieuChamDiem');
    }

    public function hoiDong()
    {
        return $this->belongsTo(HoiDong::class, 'MaHoiDong', 'MaHoiDong');
    }

    public function hoSoBaoVe()
    {
        return $this->belongsTo(HoSoBaoVe::class, 'MaHoSo', 'MaHoSo');
    }

    public function sinhVien()
    {
        return $this->belongsTo(SinhVien::class, 'MaSV', 'MaSV');
    }

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'MaGV', 'MaGV');
    }
}
