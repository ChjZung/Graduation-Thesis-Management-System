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

    /**
     * Xác định Mã Bộ Môn tương ứng theo Ngành học của sinh viên
     */
    public function getMaBoMon(): string
    {
        $nganh = $this->nganh ?? ($this->lop?->nganh);
        $tenNganh = mb_strtolower($nganh?->TenNganh ?? '');
        $maNganh = $nganh?->MaNganh ?? $this->MaNganh;

        if ($maNganh === '7480104' || str_contains($tenNganh, 'hệ thống thông tin')) {
            $bm = 'HTTT';
        } elseif ($maNganh === '7480202' || str_contains($tenNganh, 'an toàn')) {
            $bm = 'ATTT';
        } elseif ($maNganh === '7480101' || str_contains($tenNganh, 'khoa học máy tính')) {
            $bm = 'KHMT';
        } elseif ($maNganh === '7460112' || str_contains($tenNganh, 'dữ liệu')) {
            $bm = 'KHDL';
        } elseif ($maNganh === '7480102' || str_contains($tenNganh, 'mạng máy tính') || str_contains($tenNganh, 'truyền thông')) {
            $bm = 'MMT';
        } else {
            $bm = 'CNPM';
        }

        if (\App\Models\BoMon::where('MaBoMon', 'BM_' . $bm)->exists()) {
            return 'BM_' . $bm;
        }

        return $bm;
    }

    /**
     * Lấy thực thể Sinh viên của tài khoản đang đăng nhập
     */
    public static function getLoggedInSinhVien($user = null): ?self
    {
        $user = $user ?: \Illuminate\Support\Facades\Auth::user();
        if (!$user) return null;
        return self::where('MaTK', $user->MaTK)->first();
    }

    /**
     * Kiểm tra sinh viên đã có Hội đồng và hoàn tất phiên báo cáo bảo vệ để hiển thị chức năng Kết quả
     */
    public function daCoHoiDongVaBaoCaoXong(): bool
    {
        $tv = $this->thanhVienNhoms()->where('TrangThai', 'da_tham_gia')->first();
        if (!$tv) return false;

        $hoSo = HoSoBaoVe::with('hoiDong')->where('MaNhom', $tv->MaNhom)->first();
        if (!$hoSo || empty($hoSo->MaHoiDong)) {
            return false;
        }

        // Đã có Hội đồng bảo vệ. Kiểm tra điều kiện xác nhận báo cáo xong:
        // 1. Đã có điểm tổng kết / điểm bảo vệ trong bảng KetQuaSinhVien
        if ($this->ketQuaSinhViens()->exists() || KetQuaSinhVien::where('MaHoSo', $hoSo->MaHoSo)->exists()) {
            return true;
        }

        // 2. Hoặc Hội đồng đã có phiếu chấm điểm rubric cho hồ sơ này
        if (\App\Models\PhieuChamDiem::where('MaHoSo', $hoSo->MaHoSo)->exists()) {
            return true;
        }

        // 3. Hoặc Hội đồng bảo vệ có trạng thái đã kết thúc
        if ($hoSo->hoiDong && in_array(mb_strtolower(trim((string)$hoSo->hoiDong->TrangThai)), ['đã kết thúc', 'da ket thuc', 'completed', 'hoàn thành', 'hoan thanh'])) {
            return true;
        }

        // 4. Hoặc hồ sơ bảo vệ có trạng thái đã bảo vệ, đã lưu trữ
        if (in_array($hoSo->TrangThai, ['Đã bảo vệ', 'Đã lưu trữ', 'Hoàn thành']) || !empty($hoSo->FileBanChinhSua)) {
            return true;
        }

        return false;
    }
}
