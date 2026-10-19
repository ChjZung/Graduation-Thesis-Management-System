<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiangVien extends Model
{
    use HasFactory;

    protected $table = 'GiangVien';
    protected $primaryKey = 'MaGV';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaGV',
        'MaTK',
        'HoTen',
        'NgaySinh',
        'GioiTinh',
        'Email',
        'SoDienThoai',
        'HocHam',
        'HocVi',
        'TrangThai',
        'MaBoMon',
        'ChuKy',
    ];

    public $timestamps = true;

    // Backward compatibility for Sdt
    public function getSdtAttribute()
    {
        return $this->attributes['SoDienThoai'] ?? null;
    }

    public function setSdtAttribute($value)
    {
        $this->attributes['SoDienThoai'] = $value;
    }

    public function boMon()
    {
        return $this->belongsTo(BoMon::class, 'MaBoMon', 'MaBoMon');
    }

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'MaTK', 'MaTK');
    }

    public function taiKhoans()
    {
        return $this->hasMany(TaiKhoan::class, 'MaGV', 'MaGV');
    }

    public function deTais()
    {
        return $this->hasMany(DeTai::class, 'MaGV', 'MaGV');
    }

    public function deTai()
    {
        return $this->deTais();
    }

    public function chiTieuHuongDans()
    {
        return $this->hasMany(ChiTieuHuongDan::class, 'MaGV', 'MaGV');
    }

    public function phanCongPhanBiens()
    {
        return $this->hasMany(PhanCongPhanBien::class, 'MaGV', 'MaGV');
    }

    public function lichGaps()
    {
        return $this->hasMany(LichGapHuongDan::class, 'MaGV', 'MaGV');
    }

    public function thanhVienHoiDongs()
    {
        return $this->hasMany(ThanhVienHoiDong::class, 'MaGV', 'MaGV');
    }

    public function phieuChamDiems()
    {
        return $this->hasMany(PhieuChamDiem::class, 'MaGV', 'MaGV');
    }

    /**
     * Tra cứu an toàn hồ sơ giảng viên của tài khoản đang đăng nhập
     */
    public static function getLoggedInGiangVien(?TaiKhoan $user = null): ?self
    {
        $user = $user ?? \Illuminate\Support\Facades\Auth::user();
        if (!$user) return null;

        // 1. Kiểm tra trực tiếp trường MaGV lưu trên bảng TaiKhoan
        if (!empty($user->MaGV)) {
            $gv = static::with('boMon.khoa')->where('MaGV', $user->MaGV)->first();
            if ($gv) return $gv;
        }

        // 2. Tra cứu qua MaTK hoặc TenDangNhap
        $gv = static::with('boMon.khoa')
            ->where('MaTK', $user->MaTK)
            ->orWhere('MaGV', $user->TenDangNhap)
            ->orWhere('MaGV', $user->MaTK)
            ->first();
        if ($gv) return $gv;

        // 3. Tra cứu bằng số hiệu GV trong TenDangNhap (hỗ trợ cả GV002 và GV00000002)
        if (preg_match('/GV0*(\d+)/i', $user->TenDangNhap, $matches)) {
            $num = (int)$matches[1];
            $formatted8 = sprintf('GV%08d', $num);
            $gv = static::with('boMon.khoa')->where('MaGV', $formatted8)->first();
            if ($gv) return $gv;

            $gv = static::with('boMon.khoa')->where('MaGV', 'like', "%{$matches[1]}")->first();
            if ($gv) return $gv;
        }

        return null;
    }
}
