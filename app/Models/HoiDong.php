<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HoiDong extends Model
{
    use HasFactory;

    protected $table = 'HoiDong';
    protected $primaryKey = 'MaHoiDong';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaHoiDong',
        'TenHoiDong',
        'ThoiGianBatDau',
        'ThoiGianKetThuc',
        'DiaDiem',
        'TrangThai',
        'GhiChu',
        'NgayBaoVe',
        'MaDeTai',
        'MaGV',
        'MaHocKy',
        'MaKhoa',
        'MaNganh',
        'DotBaoVe',
        'SoQuyetDinh',
        'NgayBanHanh',
        'NguoiKy',
        'ChucVuNguoiKy',
        'FileQuyetDinh',
        'TrangThaiCongBo',
        'NgayCongBo',
        'NguoiCongBo',
    ];

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'ThoiGianBatDau' => 'datetime',
            'ThoiGianKetThuc' => 'datetime',
            'NgayBaoVe' => 'date',
            'NgayBanHanh' => 'date',
            'NgayCongBo' => 'datetime',
        ];
    }

    public function khoa()
    {
        return $this->belongsTo(Khoa::class, 'MaKhoa', 'MaKhoa');
    }

    public function nganh()
    {
        return $this->belongsTo(Nganh::class, 'MaNganh', 'MaNganh');
    }

    public function deTai()
    {
        return $this->belongsTo(DeTai::class, 'MaDeTai', 'MaDeTai');
    }

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'MaGV', 'MaGV');
    }

    public function hocKy()
    {
        return $this->belongsTo(HocKy::class, 'MaHocKy', 'MaHocKy');
    }

    public function thanhVienHoiDongs()
    {
        return $this->hasMany(ThanhVienHoiDong::class, 'MaHoiDong', 'MaHoiDong');
    }

    public function thanhViens()
    {
        return $this->thanhVienHoiDongs();
    }

    public function giangViens()
    {
        return $this->belongsToMany(GiangVien::class, 'ThanhVienHoiDong', 'MaHoiDong', 'MaGV')
                    ->withPivot('VaiTro')
                    ->withTimestamps();
    }

    public function hoSoBaoVes()
    {
        return $this->hasMany(HoSoBaoVe::class, 'MaHoiDong', 'MaHoiDong');
    }

    public function phieuChamDiems()
    {
        return $this->hasMany(PhieuChamDiem::class, 'MaHoiDong', 'MaHoiDong');
    }

    /**
     * Hạn chót chấm điểm mặc định sau khi phiên bảo vệ kết thúc (tính theo ngày).
     */
    const HAN_CHAM_DIEM_MAC_DINH_NGAY = 7;

    /**
     * Lấy thời gian hiện tại chuẩn của hệ thống (hỗ trợ cả Time Machine giờ ảo, test now và giờ thực)
     */
    public static function getSystemNow(): \Carbon\Carbon
    {
        if (\Carbon\Carbon::hasTestNow()) {
            $testNow = \Carbon\Carbon::getTestNow();
            return ($testNow instanceof \Carbon\Carbon ? $testNow->copy() : \Carbon\Carbon::parse($testNow))->setTimezone('Asia/Ho_Chi_Minh');
        }

        $mockFilePath = storage_path('framework/mock_time.txt');
        if (file_exists($mockFilePath)) {
            $val = trim(@file_get_contents($mockFilePath));
            if (!empty($val)) {
                try {
                    return \Carbon\Carbon::parse($val, 'Asia/Ho_Chi_Minh');
                } catch (\Throwable $e) {}
            }
        }

        if (function_exists('session') && session()->has('mock_system_time')) {
            $val = session('mock_system_time');
            if (!empty($val)) {
                try {
                    return \Carbon\Carbon::parse($val, 'Asia/Ho_Chi_Minh');
                } catch (\Throwable $e) {}
            }
        }

        return \Carbon\Carbon::now('Asia/Ho_Chi_Minh');
    }

    /**
     * Thời điểm bắt đầu chính xác của buổi bảo vệ (Múi giờ Asia/Ho_Chi_Minh)
     */
    public function getThoiDiemBatDauAttribute(): ?\Carbon\Carbon
    {
        if (!$this->NgayBaoVe && empty($this->ThoiGianBatDau)) {
            return null;
        }

        $dateStr = $this->NgayBaoVe instanceof \Carbon\Carbon
            ? $this->NgayBaoVe->format('Y-m-d')
            : ($this->NgayBaoVe ? \Carbon\Carbon::parse($this->NgayBaoVe)->format('Y-m-d') : null);

        if (!empty($this->ThoiGianBatDau)) {
            $startTime = $this->ThoiGianBatDau instanceof \Carbon\Carbon
                ? $this->ThoiGianBatDau->format('H:i:s')
                : \Carbon\Carbon::parse($this->ThoiGianBatDau)->format('H:i:s');
            if ($dateStr) {
                return \Carbon\Carbon::parse("{$dateStr} {$startTime}", 'Asia/Ho_Chi_Minh');
            }
            return \Carbon\Carbon::parse($this->ThoiGianBatDau, 'Asia/Ho_Chi_Minh');
        }

        // Mặc định bắt đầu lúc 07:30
        return \Carbon\Carbon::parse("{$dateStr} 07:30:00", 'Asia/Ho_Chi_Minh');
    }

    /**
     * Thời điểm kết thúc chính xác của buổi bảo vệ (Múi giờ Asia/Ho_Chi_Minh)
     */
    public function getThoiDiemKetThucAttribute(): ?\Carbon\Carbon
    {
        if (!$this->NgayBaoVe && empty($this->ThoiGianKetThuc)) {
            return null;
        }

        $dateStr = $this->NgayBaoVe instanceof \Carbon\Carbon
            ? $this->NgayBaoVe->format('Y-m-d')
            : ($this->NgayBaoVe ? \Carbon\Carbon::parse($this->NgayBaoVe)->format('Y-m-d') : null);

        if (!empty($this->ThoiGianKetThuc)) {
            $endTime = $this->ThoiGianKetThuc instanceof \Carbon\Carbon
                ? $this->ThoiGianKetThuc->format('H:i:s')
                : \Carbon\Carbon::parse($this->ThoiGianKetThuc)->format('H:i:s');
            if ($dateStr) {
                return \Carbon\Carbon::parse("{$dateStr} {$endTime}", 'Asia/Ho_Chi_Minh');
            }
            return \Carbon\Carbon::parse($this->ThoiGianKetThuc, 'Asia/Ho_Chi_Minh');
        }

        // Mặc định kết thúc lúc 17:00
        return \Carbon\Carbon::parse("{$dateStr} 17:00:00", 'Asia/Ho_Chi_Minh');
    }

    /**
     * Trạng thái thời gian chuẩn duy nhất của Hội đồng: 'SapDienRa', 'DangDienRa', 'DaKetThuc'
     * Quy tắc:
     * - Sắp diễn ra: bây giờ < thời điểm bắt đầu
     * - Đang diễn ra: bắt đầu <= bây giờ <= kết thúc
     * - Đã kết thúc: bây giờ > kết thúc
     */
    public function getTrangThaiThoiGianAttribute(): string
    {
        $now = self::getSystemNow();
        $start = $this->thoi_diem_bat_dau;
        $end = $this->thoi_diem_ket_thuc;

        if (!$start) {
            return 'SapDienRa';
        }

        if ($now->lt($start)) {
            return 'SapDienRa';
        }

        if ($end && $now->gt($end)) {
            return 'DaKetThuc';
        }

        return 'DangDienRa';
    }

    /**
     * Tên tiếng Việt hiển thị của trạng thái thời gian (chuẩn hóa đồng nhất)
     */
    public function getTenTrangThaiThoiGianAttribute(): string
    {
        return match($this->trang_thai_thoi_gian) {
            'DangDienRa' => 'Đang Diễn Ra',
            'DaKetThuc'  => 'Đã Kết Thúc',
            default      => 'Sắp Diễn Ra',
        };
    }

    /**
     * Thời hạn chót được phép chấm điểm (kết thúc + số ngày cấu hình, mặc định 7 ngày)
     */
    public function getHanChamDiemAttribute(): ?\Carbon\Carbon
    {
        $end = $this->thoi_diem_ket_thuc ?? $this->thoi_diem_bat_dau;
        if (!$end) {
            return null;
        }

        $days = (int) config('services.kltn.han_cham_diem_ngay', self::HAN_CHAM_DIEM_MAC_DINH_NGAY);
        return $end->copy()->addDays($days)->endOfDay();
    }

    /**
     * Đếm ngược thời gian cho hội đồng sắp diễn ra (vd: "còn 6 ngày", "còn 2 giờ")
     */
    public function getDemNguocBatDauAttribute(): ?string
    {
        $now = self::getSystemNow();
        $start = $this->thoi_diem_bat_dau;

        if (!$start || $now->gte($start)) {
            return null;
        }

        $diffInDays = (int) floor($now->diffInDays($start));
        if ($diffInDays >= 1) {
            return "còn {$diffInDays} ngày";
        }

        $diffInHours = (int) floor($now->diffInHours($start));
        if ($diffInHours >= 1) {
            return "còn {$diffInHours} giờ";
        }

        $diffInMinutes = (int) max(1, floor($now->diffInMinutes($start)));
        return "còn {$diffInMinutes} phút";
    }

    /**
     * Kiểm tra xem Hội đồng có đang trong khung thời gian được phép chấm điểm hay không
     * Cho phép khi "Đang diễn ra", hoặc "Đã kết thúc" còn trong hạn 7 ngày và chưa công bố chính thức
     */
    public function isWithinGradingWindow(?\Carbon\Carbon $now = null): bool
    {
        $now = $now ?? self::getSystemNow();
        $tt = $this->trang_thai_thoi_gian;

        // 1. Sắp diễn ra -> Tuyệt đối không được chấm
        if ($tt === 'SapDienRa') {
            return false;
        }

        // 2. Đang diễn ra: bắt đầu <= now <= kết thúc -> Luôn cho phép chấm điểm
        if ($tt === 'DangDienRa') {
            return true;
        }

        // 3. Đã kết thúc:
        // Đã công bố kết quả chính thức toàn hội đồng -> Khóa chấm
        if ($this->TrangThaiCongBo === 'Đã công bố') {
            return false;
        }

        // Cho phép nếu còn trong hạn chấm (mặc định 7 ngày sau kết thúc)
        $deadline = $this->han_cham_diem;
        if ($deadline && $now->lte($deadline)) {
            return true;
        }

        return false;
    }

    /**
     * Kiểm tra quyền chấm điểm toàn diện của người dùng đối với Hội đồng
     * Dùng chung cho Controller, Form Request, Policy, View và API
     */
    public function canGrade($userOrGiangVien, ?HoSoBaoVe $hoSo = null, ?string &$reason = null): bool
    {
        $now = self::getSystemNow();

        $user = null;
        $giangVien = null;

        if (is_string($userOrGiangVien)) {
            $giangVien = \App\Models\GiangVien::find($userOrGiangVien);
            $user = $giangVien?->taiKhoan;
        } elseif ($userOrGiangVien instanceof \App\Models\TaiKhoan) {
            $user = $userOrGiangVien;
            $giangVien = \App\Models\GiangVien::getLoggedInGiangVien($user);
        } elseif ($userOrGiangVien instanceof \App\Models\GiangVien) {
            $giangVien = $userOrGiangVien;
            $user = $giangVien->taiKhoan;
        }

        // Quản trị viên (Trưởng khoa / Admin) có quyền đặc biệt nếu cần xem xét
        $isManager = false;
        if ($user) {
            $isManager = (method_exists($user, 'isAdmin') && $user->isAdmin()) 
                      || (method_exists($user, 'isTruongKhoa') && $user->isTruongKhoa());
        }

        // 1. Kiểm tra thành viên hội đồng
        if (!$isManager) {
            if (!$giangVien) {
                $reason = 'Không tìm thấy hồ sơ giảng viên của tài khoản này.';
                return false;
            }

            $isMember = $this->thanhViens->contains(fn($tv) => $tv->MaGV === $giangVien->MaGV);
            if (!$isMember) {
                $reason = 'Bạn không thuộc Hội đồng bảo vệ này và không có quyền chấm điểm.';
                return false;
            }
        }

        // 2. Kiểm tra theo trạng thái thời gian chuẩn
        $tt = $this->trang_thai_thoi_gian;
        $start = $this->thoi_diem_bat_dau;

        // Sắp diễn ra: Khóa hoàn toàn
        if ($tt === 'SapDienRa') {
            $openTime = $start ? $start->format('H:i \n\g\à\y d/m/Y') : 'thời gian quy định';
            $reason = "Hội đồng chưa đến thời gian chấm điểm. Mở chấm điểm từ {$openTime}.";
            return false;
        }

        // Đã kết thúc: Kiểm tra công bố và hạn 7 ngày
        if ($tt === 'DaKetThuc') {
            if ($this->TrangThaiCongBo === 'Đã công bố') {
                $reason = 'Hội đồng đã công bố kết quả chính thức. Bảng điểm đã được khóa.';
                return false;
            }

            $deadline = $this->han_cham_diem;
            if ($deadline && $now->gt($deadline)) {
                $reason = "Đã quá thời hạn chấm điểm Hội đồng (Hạn chót: {$deadline->format('d/m/Y H:i')}).";
                return false;
            }
        }

        // Đang diễn ra: Luôn bật quyền chấm điểm cho thành viên!

        // 3. Kiểm tra theo Hồ sơ bảo vệ cụ thể (nếu có)
        if ($hoSo) {
            if ($hoSo->MaHoiDong !== $this->MaHoiDong) {
                $reason = 'Hồ sơ bảo vệ không thuộc Hội đồng bảo vệ này.';
                return false;
            }

            // Nếu đề tài đã công bố điểm sau khi kết thúc
            if ($tt === 'DaKetThuc' && in_array($hoSo->TrangThaiKetQua, ['DaCongBo', 'DaHoanThanh'])) {
                $reason = 'Kết quả bảo vệ của đề tài này đã được công bố chính thức.';
                return false;
            }

            // Phiếu điểm của giảng viên này đã ký xác nhận & khóa hoàn thành
            if ($giangVien) {
                $phieu = \App\Models\PhieuChamDiem::where('MaHoiDong', $this->MaHoiDong)
                    ->where('MaHoSo', $hoSo->MaHoSo)
                    ->where('MaGV', $giangVien->MaGV)
                    ->first();
                if ($phieu && $phieu->TrangThai === 'Đã hoàn thành') {
                    $reason = 'Phiếu chấm điểm của Thầy/Cô đã được xác nhận và khóa.';
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Danh sách thành viên sắp xếp theo thứ tự cố định: Chủ tịch -> Phản biện -> Thư ký -> Ủy viên
     */
    public function getSortedThanhViensAttribute()
    {
        return $this->thanhViens->sortBy('role_priority')->values();
    }
}
