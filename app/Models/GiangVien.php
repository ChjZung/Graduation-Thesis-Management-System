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

    public function hoiDongs()
    {
        return $this->belongsToMany(HoiDong::class, 'ThanhVienHoiDong', 'MaGV', 'MaHoiDong')
                    ->withPivot('VaiTro')
                    ->withTimestamps();
    }

    public function phieuChamDiems()
    {
        return $this->hasMany(PhieuChamDiem::class, 'MaGV', 'MaGV');
    }

    /**
     * Tên đầy đủ kèm học hàm, học vị chuẩn (không bị lặp danh xưng):
     * Ví dụ: "PGS.TS. Nguyễn Văn Hùng", "TS. Lê Hoàng Cường"
     */
    public function getDanhXungHoTenAttribute(): string
    {
        $hh = trim($this->HocHam ?? '');
        $hv = trim($this->HocVi ?? '');

        $prefix = '';
        if (mb_stripos($hh, 'giáo sư') !== false && mb_stripos($hh, 'phó') === false) {
            $prefix = 'GS.';
        } elseif (mb_stripos($hh, 'phó giáo sư') !== false || mb_stripos($hh, 'pgs') !== false) {
            $prefix = 'PGS.';
        }

        if (mb_stripos($hv, 'tiến sĩ') !== false || mb_stripos($hv, 'ts') !== false || mb_stripos($hv, 'tskh') !== false) {
            $prefix .= 'TS.';
        } elseif (mb_stripos($hv, 'thạc sĩ') !== false || mb_stripos($hv, 'ths') !== false) {
            $prefix .= 'ThS.';
        } elseif (empty($prefix)) {
            if (mb_stripos($hv, 'kỹ sư') !== false || mb_stripos($hv, 'ks') !== false) {
                $prefix = 'KS.';
            } elseif (mb_stripos($hv, 'cử nhân') !== false || mb_stripos($hv, 'cn') !== false) {
                $prefix = 'CN.';
            } else {
                $prefix = 'GV.';
            }
        }

        $hoTen = trim($this->HoTen ?? '');
        $cleanHoTen = preg_replace('/^(PGS\.TS\.|GS\.TS\.|PGS\.|GS\.|TSKH\.|TS\.|ThS\.|KS\.|CN\.|GV\.|PGS|GS|TSKH|TS|ThS|KS|CN|GV)\s+/ui', '', $hoTen);
        $cleanHoTen = preg_replace('/^(PGS\.TS\.|GS\.TS\.|PGS\.|GS\.|TSKH\.|TS\.|ThS\.|KS\.|CN\.|GV\.|PGS|GS|TSKH|TS|ThS|KS|CN|GV)\s+/ui', '', $cleanHoTen);

        return trim($prefix . ' ' . $cleanHoTen);
    }

    /**
     * Nhãn danh xưng ngắn gọn và chuyên nghiệp cho select option:
     * Ví dụ: "PGS.TS. Nguyễn Văn Hùng (GV00000001)", "TS. Lê Hoàng Cường (GV00000023)"
     */
    public function getDanhXungOptionAttribute(): string
    {
        return $this->danh_xung_ho_ten . ' (' . $this->MaGV . ')';
    }

    /**
     * Lấy danh sách giảng viên đã nhóm theo bộ môn và sắp xếp theo chuẩn:
     * 1. Tên Bộ môn A -> Z (nhóm "Chưa phân bộ môn" ở cuối cùng)
     * 2. Trong từng bộ môn: Sắp xếp theo Học vị/Học hàm (PGS.TS -> TS -> ThS -> Khác)
     * 3. Cùng học vị: Sắp xếp theo Tên tiếng Việt (A -> Z)
     */
    public static function getGroupedByBoMonForSelect(?string $maKhoa = null): \Illuminate\Support\Collection
    {
        $query = static::with('boMon')
            ->where(function($q) {
                $q->whereNull('TrangThai')
                  ->orWhere('TrangThai', 'Đang công tác')
                  ->orWhere('TrangThai', 'Hoạt động');
            });

        if ($maKhoa) {
            $query->whereHas('boMon', fn($bm) => $bm->where('MaKhoa', $maKhoa));
        }

        $giangViens = $query->get();
        if ($giangViens->isEmpty() && $maKhoa) {
            $giangViens = static::with('boMon')->get();
        }

        $collator = class_exists('\Collator') ? new \Collator('vi_VN') : null;
        $compareVi = function(string $a, string $b) use ($collator): int {
            if ($collator) {
                return $collator->compare($a, $b);
            }
            return strcasecmp($a, $b);
        };

        $getAcademicRank = function($gv): int {
            $hh = mb_strtolower($gv->HocHam ?? '');
            $hv = mb_strtolower($gv->HocVi ?? '');

            if (str_contains($hh, 'giáo sư') && !str_contains($hh, 'phó')) return 1;
            if (str_contains($hh, 'pgs') || str_contains($hh, 'phó giáo sư')) return 2;
            if (str_contains($hv, 'tiến sĩ') || str_contains($hv, 'ts') || str_contains($hv, 'tskh')) return 3;
            if (str_contains($hv, 'thạc sĩ') || str_contains($hv, 'ths')) return 4;
            return 5;
        };

        $getVietnameseNameParts = function(string $fullName): array {
            $parts = preg_split('/\s+/u', trim($fullName));
            if (empty($parts)) return ['', ''];
            $firstName = array_pop($parts);
            $lastName = implode(' ', $parts);
            return [$firstName, $lastName];
        };

        // Nhóm theo tên Bộ môn
        $grouped = $giangViens->groupBy(function($gv) {
            return $gv->boMon?->TenBoMon ?: 'Chưa phân bộ môn';
        });

        // Sắp xếp các giảng viên bên trong mỗi bộ môn
        $sortedGrouped = $grouped->map(function(\Illuminate\Support\Collection $items) use ($getAcademicRank, $getVietnameseNameParts, $compareVi) {
            return $items->sort(function($a, $b) use ($getAcademicRank, $getVietnameseNameParts, $compareVi) {
                $rankA = $getAcademicRank($a);
                $rankB = $getAcademicRank($b);
                if ($rankA !== $rankB) {
                    return $rankA <=> $rankB;
                }

                [$firstA, $lastA] = $getVietnameseNameParts($a->HoTen ?? '');
                [$firstB, $lastB] = $getVietnameseNameParts($b->HoTen ?? '');

                $firstCmp = $compareVi($firstA, $firstB);
                if ($firstCmp !== 0) {
                    return $firstCmp;
                }

                return $compareVi($lastA, $lastB);
            })->values();
        });

        // Sắp xếp các bộ môn (A->Z), nhưng "Chưa phân bộ môn" luôn ở cuối
        $sortedKeys = $sortedGrouped->keys()->sort(function($a, $b) use ($compareVi) {
            if ($a === 'Chưa phân bộ môn') return 1;
            if ($b === 'Chưa phân bộ môn') return -1;
            return $compareVi($a, $b);
        });

        $result = collect();
        foreach ($sortedKeys as $key) {
            $result->put($key, $sortedGrouped->get($key));
        }

        return $result;
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
