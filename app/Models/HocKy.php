<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HocKy extends Model
{
    use HasFactory;

    protected $table = 'HocKy';
    protected $primaryKey = 'MaHocKy';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaHocKy',
        'TenHocKy',
        'NamHoc',
        'NgayDiHoc',
        'NgayBatDau',
        'NgayKetThuc',
        'TrangThai',
    ];

    public $timestamps = true;

    // Backward compatibility for NgayBatDau
    public function getNgayBatDauAttribute()
    {
        return $this->attributes['NgayDiHoc'] ?? null;
    }

    public function setNgayBatDauAttribute($value)
    {
        $this->attributes['NgayDiHoc'] = $value;
    }

    public function getTenDayDuAttribute(): string
    {
        return $this->TenHocKy . ($this->NamHoc ? ' (' . $this->NamHoc . ')' : '');
    }

    /**
     * Xác định Học kỳ hiện tại theo chuẩn nghiệp vụ:
     * 1. Tìm học kỳ có khoảng thời gian [NgayDiHoc, NgayKetThuc] bao phủ ngày hiện tại (có tính Giờ Ảo).
     * 2. Nếu không có (giữa hai kỳ) hoặc chưa cấu hình ngày: tìm học kỳ có TrangThai = 'Đang diễn ra'.
     * 3. Nếu vẫn không có: ưu tiên học kỳ sắp diễn ra gần nhất (NgayDiHoc > now(), asc).
     * 4. Fallback: học kỳ vừa kết thúc gần nhất (NgayKetThuc < now(), desc), hoặc học kỳ mới nhất theo created_at.
     */
    public static function getHocKyHienTai(): ?self
    {
        $today = now()->toDateString();

        // 1. Khoảng ngày bao phủ hôm nay (bao gồm giờ ảo)
        $hk = static::whereNotNull('NgayDiHoc')
            ->whereNotNull('NgayKetThuc')
            ->where('NgayDiHoc', '<=', $today)
            ->where('NgayKetThuc', '>=', $today)
            ->first();

        if ($hk) {
            return $hk;
        }

        // 2. Giữa hai kỳ / Ngày hiện tại nằm ngoài các kỳ: ưu tiên học kỳ sắp diễn ra gần nhất
        $hk = static::whereNotNull('NgayDiHoc')
            ->where('NgayDiHoc', '>', $today)
            ->orderBy('NgayDiHoc', 'asc')
            ->first();
        if ($hk) {
            return $hk;
        }

        // 3. Theo cờ Trạng Thái 'Đang diễn ra' (nếu kỳ chưa nhập ngày bắt đầu/kết thúc)
        $hk = static::whereIn('TrangThai', ['Đang diễn ra', '1', 'Hoạt động'])->first();
        if ($hk) {
            return $hk;
        }

        // 4. Fallback: học kỳ vừa kết thúc gần nhất
        $hk = static::whereNotNull('NgayKetThuc')
            ->where('NgayKetThuc', '<', $today)
            ->orderBy('NgayKetThuc', 'desc')
            ->first();
        if ($hk) {
            return $hk;
        }

        // 5. Fallback cuối cùng
        return static::orderBy('created_at', 'desc')->first();
    }

    public function deTais()
    {
        return $this->hasMany(DeTai::class, 'MaHocKy', 'MaHocKy');
    }

    public function danhSachSVDuDieuKiens()
    {
        return $this->hasMany(DanhSachSVDuDieuKien::class, 'MaHocKy', 'MaHocKy');
    }

    public function chiTieuHuongDans()
    {
        return $this->hasMany(ChiTieuHuongDan::class, 'MaHocKy', 'MaHocKy');
    }

    public function keHoachKhoaLuans()
    {
        return $this->hasMany(KeHoachKhoaLuan::class, 'MaHocKy', 'MaHocKy');
    }

    public function keHoachKhoaLuan()
    {
        return $this->hasOne(KeHoachKhoaLuan::class, 'MaHocKy', 'MaHocKy')->latest();
    }

    public function hoiDongs()
    {
        return $this->hasMany(HoiDong::class, 'MaHocKy', 'MaHocKy');
    }

    public function ketQuaSinhViens()
    {
        return $this->hasMany(KetQuaSinhVien::class, 'MaHocKy', 'MaHocKy');
    }

    public function hocPhans()
    {
        return $this->belongsToMany(HocPhan::class, 'HocPhan_HocKy', 'MaHocKy', 'MaHocPhan')
                    ->withPivot(['id', 'TrangThai', 'GhiChu'])
                    ->withTimestamps();
    }

    public function hocPhanHocKies()
    {
        return $this->hasMany(HocPhanHocKy::class, 'MaHocKy', 'MaHocKy');
    }
}
