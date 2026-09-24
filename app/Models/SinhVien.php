<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SinhVien extends Model
{
    use HasFactory;

    protected $table = 'SinhVien';
    protected $primaryKey = 'MaSV';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaSV',
        'MaTK',
        'HoTen',
        'NgaySinh',
        'GioiTinh',
        'Email',
        'SoDienThoai',
        'NgayNhapHoc',
        'KhoaHoc',
        'SoTinChiTichLuy',
        'DiemTichLuy',
        'TrangThai',
        'MaKhoa',
        'MaNganh',
        'MaLop',
    ];

    public $timestamps = true;

    // Backward compatibility for MaSoSinhVien
    public function getMaSoSinhVienAttribute()
    {
        return $this->attributes['MaSV'] ?? null;
    }

    public function lop()
    {
        return $this->belongsTo(Lop::class, 'MaLop', 'MaLop');
    }

    public function nganh()
    {
        return $this->belongsTo(Nganh::class, 'MaNganh', 'MaNganh');
    }

    public function khoa()
    {
        return $this->belongsTo(Khoa::class, 'MaKhoa', 'MaKhoa');
    }

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'MaTK', 'MaTK');
    }

    public function thanhVienNhoms()
    {
        return $this->hasMany(ThanhVienNhom::class, 'MaSV', 'MaSV');
    }

    public function nhoms()
    {
        return $this->belongsToMany(Nhom::class, 'ThanhVienNhom', 'MaSV', 'MaNhom')
                    ->withPivot('VaiTro', 'NgayThamGia')
                    ->withTimestamps();
    }

    public function danhSachSVDuDieuKiens()
    {
        return $this->hasMany(DanhSachSVDuDieuKien::class, 'MaSV', 'MaSV');
    }

    public function ketQuaSinhViens()
    {
        return $this->hasMany(KetQuaSinhVien::class, 'MaSV', 'MaSV');
    }

    /**
     * Kiểm tra sinh viên có đủ điều kiện làm khóa luận tốt nghiệp không
     * Tiêu chí:
     * - Có trong DanhSachSVDuDieuKien với TrangThai = 'Đủ điều kiện' HOẶC
     * - Đạt tiêu chuẩn Khoa: Tích lũy >= 115 tín chỉ, GPA >= 2.0 và đang học
     */
    public function isDuDieuKien(): bool
    {
        $daDuyet = $this->danhSachSVDuDieuKiens()->where('TrangThai', 'Đủ điều kiện')->exists();
        if ($daDuyet) {
            return true;
        }

        $tinChi = (int)($this->SoTinChiTichLuy ?? 0);
        $gpa = (float)($this->DiemTichLuy ?? 0.0);
        $trangThai = $this->TrangThai ?? 'Đang học';

        return ($tinChi >= 115) && ($gpa >= 2.0) && in_array($trangThai, ['Đang học', 'Đủ điều kiện']);
    }

    public function getIsDuDieuKienAttribute(): bool
    {
        return $this->isDuDieuKien();
    }
}
