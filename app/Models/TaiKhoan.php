<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class TaiKhoan extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'TaiKhoan';
    protected $primaryKey = 'MaTK';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaTK',
        'MaVaiTro',
        'MaGV',
        'TenDangNhap',
        'MatKhau',
        'TrangThai',
        'SoLanDangNhapSai',
        'BatBuocDoiMatKhau',
        'TrangThaiMatKhau',
        'LanDangNhapDau',
        'NgayDoiMatKhau',
        'LanDangNhapCuoi',
        'NgayKhoa',
        'remember_token',
        // Compatibility aliases
        'password_status',
        'password_changed_at',
        'first_login_at',
    ];

    protected $hidden = [
        'MatKhau',
        'remember_token',
    ];

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'LanDangNhapDau'    => 'datetime',
            'NgayDoiMatKhau'    => 'datetime',
            'LanDangNhapCuoi'   => 'datetime',
            'NgayKhoa'          => 'datetime',
            'TrangThai'         => 'boolean',
            'BatBuocDoiMatKhau' => 'boolean',
        ];
    }

    // Backward compatibility accessors/mutators for existing code
    public function getPasswordStatusAttribute()
    {
        return $this->attributes['TrangThaiMatKhau'] ?? 'INITIAL';
    }

    public function setPasswordStatusAttribute($value)
    {
        $this->attributes['TrangThaiMatKhau'] = $value;
    }

    public function getFirstLoginAtAttribute()
    {
        return $this->attributes['LanDangNhapDau'] ?? null;
    }

    public function setFirstLoginAtAttribute($value)
    {
        $this->attributes['LanDangNhapDau'] = $value;
    }

    public function getPasswordChangedAtAttribute()
    {
        return $this->attributes['NgayDoiMatKhau'] ?? null;
    }

    public function setPasswordChangedAtAttribute($value)
    {
        $this->attributes['NgayDoiMatKhau'] = $value;
    }

    public function getAuthPasswordName()
    {
        return 'MatKhau';
    }

    public function getAuthPassword()
    {
        return $this->MatKhau;
    }

    public function isPasswordInitial(): bool
    {
        return ($this->TrangThaiMatKhau === 'INITIAL' || $this->BatBuocDoiMatKhau === true);
    }

    public function isPasswordActive(): bool
    {
        return ($this->TrangThaiMatKhau === 'ACTIVE' && !$this->BatBuocDoiMatKhau);
    }

    public function isPasswordExpired(): bool
    {
        return ($this->TrangThaiMatKhau === 'EXPIRED');
    }

    /**
     * Kiểm tra tài khoản có đang bị khóa (bởi Admin hoặc tự động do nhập sai >= 5 lần).
     */
    public function isLocked(): bool
    {
        // 1. Khóa thủ công hoặc khóa tự động
        if (!$this->TrangThai || (int)$this->SoLanDangNhapSai >= 5) {
            // Nếu là khóa tự động và đã hết hạn khóa tạm (nếu cấu hình)
            if ($this->isAutoLocked() && $this->isAutoLockExpired()) {
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * Kiểm tra tài khoản bị khóa thủ công bởi Quản trị viên (TrangThai = false nhưng SoLanDangNhapSai < 5).
     */
    public function isManuallyLocked(): bool
    {
        return !$this->TrangThai && ((int)$this->SoLanDangNhapSai < 5);
    }

    /**
     * Kiểm tra tài khoản bị khóa tự động do nhập sai mật khẩu >= 5 lần.
     */
    public function isAutoLocked(): bool
    {
        return (int)$this->SoLanDangNhapSai >= 5;
    }

    /**
     * Kiểm tra xem khóa tự động đã hết hạn hay chưa (chỉ áp dụng cho khóa tự động, không áp dụng cho khóa thủ công).
     */
    public function isAutoLockExpired(int $timeoutMinutes = 0): bool
    {
        if ($this->isManuallyLocked()) {
            return false; // Khóa thủ công TUYỆT ĐỐI không bao giờ tự hết hạn
        }
        if ($timeoutMinutes <= 0) {
            return false; // Chính sách mặc định: Cần Quản trị viên mở khóa
        }
        if (!$this->NgayKhoa) {
            return false;
        }
        return now()->diffInMinutes($this->NgayKhoa) >= $timeoutMinutes;
    }

    /**
     * Mở khóa tài khoản an toàn & nguyên tử.
     * Reset TrangThai = true, SoLanDangNhapSai = 0, NgayKhoa = null, giữ nguyên mật khẩu.
     */
    public function unlock(?string $performedBy = null): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($performedBy) {
            $this->TrangThai = true;
            $this->SoLanDangNhapSai = 0;
            $this->NgayKhoa = null;
            $this->save();

            \App\Models\AuditLog::log('UNLOCK_ACCOUNT', 'TaiKhoan', $this->MaTK, [
                'performed_by' => $performedBy ?? auth()->user()?->TenDangNhap ?? 'admin',
                'action'       => 'Reset status to active, failed attempts to 0, cleared lock time'
            ]);
        });
    }

    /**
     * Khóa tài khoản (Thủ công hoặc Tự động).
     */
    public function lock(bool $isAuto = false, ?string $reason = null): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($isAuto, $reason) {
            $this->TrangThai = false;
            $this->NgayKhoa = now();
            if ($isAuto) {
                $this->SoLanDangNhapSai = max(5, (int)$this->SoLanDangNhapSai);
            }
            $this->save();

            \App\Models\AuditLog::log($isAuto ? 'AUTO_LOCK' : 'MANUAL_LOCK', 'TaiKhoan', $this->MaTK, [
                'reason'       => $reason ?? ($isAuto ? 'Exceeded 5 failed login attempts' : 'Admin manual lock'),
                'performed_by' => auth()->user()?->TenDangNhap ?? ($isAuto ? 'system' : 'admin')
            ]);
        });
    }

    /**
     * Ghi nhận 1 lần đăng nhập sai một cách an toàn và chống race condition.
     */
    public function recordFailedLogin(): int
    {
        return \Illuminate\Support\Facades\DB::transaction(function () {
            $tk = static::where('MaTK', $this->MaTK)->lockForUpdate()->first();
            if (!$tk) return 0;

            // Nếu đã bị khóa thì giữ nguyên
            if (!$tk->TrangThai || (int)$tk->SoLanDangNhapSai >= 5) {
                return (int)$tk->SoLanDangNhapSai;
            }

            $newCount = (int)$tk->SoLanDangNhapSai + 1;
            $tk->SoLanDangNhapSai = min(5, $newCount);

            if ($tk->SoLanDangNhapSai >= 5) {
                $tk->TrangThai = false;
                $tk->NgayKhoa = now();
                \App\Models\AuditLog::log('AUTO_LOCK', 'TaiKhoan', $tk->MaTK, [
                    'reason' => 'Exceeded 5 consecutive failed login attempts',
                    'time'   => now()->toIso8601String()
                ]);
            }
            $tk->save();

            $this->SoLanDangNhapSai = $tk->SoLanDangNhapSai;
            $this->TrangThai = $tk->TrangThai;
            $this->NgayKhoa = $tk->NgayKhoa;

            return (int)$tk->SoLanDangNhapSai;
        });
    }

    /**
     * Ghi nhận đăng nhập thành công: Reset số lần sai về 0 và lưu thời điểm đăng nhập.
     */
    public function recordSuccessfulLogin(): void
    {
        $this->update([
            'SoLanDangNhapSai' => 0,
            'LanDangNhapCuoi'  => now(),
        ]);
    }

    public function vaiTro()
    {
        return $this->belongsTo(VaiTro::class, 'MaVaiTro', 'MaVaiTro');
    }

    public function giaoVu()
    {
        return $this->hasOne(GiaoVu::class, 'MaTK', 'MaTK');
    }

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'MaGV', 'MaGV');
    }

    public function getGiangVienAttribute()
    {
        if ($this->relationLoaded('giangVien') && $this->getRelation('giangVien')) {
            return $this->getRelation('giangVien');
        }

        $gv = null;
        if (!empty($this->attributes['MaGV'])) {
            $gv = GiangVien::with('boMon.khoa')->where('MaGV', $this->attributes['MaGV'])->first();
        }
        if (!$gv) {
            $gv = GiangVien::with('boMon.khoa')
                ->where('MaTK', $this->MaTK)
                ->orWhere('MaGV', $this->TenDangNhap)
                ->first();
        }
        if (!$gv && preg_match('/(GV\d+)/i', $this->TenDangNhap, $m)) {
            $rawMa = strtoupper($m[1]);
            $num = (int)preg_replace('/\D/', '', $rawMa);
            $padded = 'GV' . str_pad($num, 8, '0', STR_PAD_LEFT);
            $gv = GiangVien::with('boMon.khoa')->where('MaGV', $padded)->orWhere('MaGV', $rawMa)->first();
        }

        if ($gv) {
            $this->setRelation('giangVien', $gv);
        }

        return $gv;
    }

    public function sinhVien()
    {
        return $this->hasOne(SinhVien::class, 'MaTK', 'MaTK');
    }
}
