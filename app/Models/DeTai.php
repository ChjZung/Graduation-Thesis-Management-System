<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DeTai extends Model
{
    use HasFactory;

    protected $table = 'DeTai';
    protected $primaryKey = 'MaDeTai';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaDeTai',
        'TenDeTai',
        'MoTa',
        'YeuCau',
        'LinhVuc',
        'SoLuongSinhVienToiDa',
        'FileDeCuong',
        'MaNganh',
        'HocPhan',
        'MaHocPhan',
        'TrangThai',
        'LyDoTuChoi',
        'NgayDeXuat',
        'NgayDuyet',
        'NgayDuyetBM',
        'NguoiDuyetBM',
        'NgayDuyetKhoa',
        'NguoiDuyetKhoa',
        'NgayCongBo',
        'MaGV',
        'MaHocKy',
        'DuLieuNhap',
    ];

    public function getBanNhapDataAttribute(): ?array
    {
        if (empty($this->DuLieuNhap)) {
            return null;
        }
        $data = json_decode($this->DuLieuNhap, true);
        return is_array($data) ? $data : null;
    }

    public function hasDraft(): bool
    {
        return !empty($this->getBanNhapDataAttribute());
    }

    public $timestamps = true;

    public function coTepDeCuong(): bool
    {
        // FileDeCuong là bản nộp hiện tại; tài liệu lịch sử/bản nháp không thay thế nó.
        $path = ltrim(str_replace('\\', '/', trim((string) $this->FileDeCuong)), '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        if ($path === '' || str_contains($path, '://') || in_array('..', explode('/', $path), true)) {
            return false;
        }

        return Storage::disk('public')->exists($path);
    }

    public function daDuyetDeCuong(): bool
    {
        if (!$this->coTepDeCuong() || !in_array($this->TrangThai, ['Đã công bố', 'Hoàn thành', 'Đã duyệt đề cương'], true)) {
            return false;
        }

        // Công bố đề xuất của Trưởng khoa chưa phải phê duyệt đề cương của Bộ môn.
        return $this->chiTietDuyets->contains(function ($duyet) {
            return $duyet->TrangThai === 'Đã công bố' && (
                $duyet->HanhDong === 'Phê duyệt đề cương & Công bố đề tài'
                || ($duyet->HanhDong === 'Phê duyệt & Công bố'
                    && $duyet->LyDo === 'Trưởng bộ môn đã duyệt đề cương và công bố đề tài.')
            );
        });
    }

    public function khoaNopDeCuong(): bool
    {
        return $this->TrangThai === 'Hoàn thành' || $this->daDuyetDeCuong();
    }

    public function scopeDaDuyetDeCuong($query)
    {
        return $query->whereNotNull('FileDeCuong')->where('FileDeCuong', '!=', '')
            ->whereIn('TrangThai', ['Đã công bố', 'Hoàn thành', 'Đã duyệt đề cương'])
            ->whereHas('chiTietDuyets', function ($duyet) {
                $duyet->where('TrangThai', 'Đã công bố')->where(function ($action) {
                    $action->where('HanhDong', 'Phê duyệt đề cương & Công bố đề tài')
                        ->orWhere(function ($legacy) {
                            $legacy->where('HanhDong', 'Phê duyệt & Công bố')
                                ->where('LyDo', 'Trưởng bộ môn đã duyệt đề cương và công bố đề tài.');
                        });
                });
            });
    }

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'MaGV', 'MaGV');
    }

    public function hocPhanRef()
    {
        return $this->belongsTo(HocPhan::class, 'MaHocPhan', 'MaHocPhan');
    }

    public function hocPhan()
    {
        return $this->belongsTo(HocPhan::class, 'MaHocPhan', 'MaHocPhan');
    }

    public function hocKy()
    {
        return $this->belongsTo(HocKy::class, 'MaHocKy', 'MaHocKy');
    }

    public function nganh()
    {
        return $this->belongsTo(Nganh::class, 'MaNganh', 'MaNganh');
    }

    public function chiTietDuyets()
    {
        return $this->hasMany(ChiTietDuyetDeTai::class, 'MaDeTai', 'MaDeTai');
    }

    public function chiTietDuyetDeTais()
    {
        return $this->hasMany(ChiTietDuyetDeTai::class, 'MaDeTai', 'MaDeTai');
    }

    public function phanCongPhanBiens()
    {
        return $this->hasMany(PhanCongPhanBien::class, 'MaDeTai', 'MaDeTai');
    }

    public function phieuDangKys()
    {
        return $this->hasMany(PhieuDangKy::class, 'MaDeTai', 'MaDeTai');
    }

    // Alias for backward compatibility
    public function dangKyDeTais()
    {
        return $this->phieuDangKys();
    }

    public function baoCaoTienDos()
    {
        return $this->hasMany(BaoCaoTienDo::class, 'MaDeTai', 'MaDeTai');
    }

    public function taiLieuNops()
    {
        return $this->hasMany(TaiLieuNop::class, 'MaDeTai', 'MaDeTai');
    }

    public function hoiDongs()
    {
        return $this->hasMany(HoiDong::class, 'MaDeTai', 'MaDeTai');
    }

    public function hoSoBaoVes()
    {
        return $this->hasMany(HoSoBaoVe::class, 'MaDeTai', 'MaDeTai');
    }

    public function phieuChamDiems()
    {
        return $this->hasMany(PhieuChamDiem::class, 'MaDeTai', 'MaDeTai');
    }

    public function boMon()
    {
        return $this->hasOneThrough(BoMon::class, GiangVien::class, 'MaGV', 'MaBoMon', 'MaGV', 'MaBoMon');
    }

    public function nhoms()
    {
        return $this->hasManyThrough(Nhom::class, PhieuDangKy::class, 'MaDeTai', 'MaNhom', 'MaDeTai', 'MaNhom');
    }

    public function giaiDoanThucHiens()
    {
        return $this->hasMany(GiaiDoanThucHien::class, 'MaDeTai', 'MaDeTai')->orderBy('ThuTu');
    }

    public function dotNopBaoCaos()
    {
        return $this->hasMany(DotNopBaoCao::class, 'MaDeTai', 'MaDeTai')->orderBy('ThuTu');
    }

    public function tienDoNghiepVu()
    {
        return $this->hasOne(TienDoNghiepVuKhoaLuan::class, 'MaDeTai', 'MaDeTai');
    }
}
