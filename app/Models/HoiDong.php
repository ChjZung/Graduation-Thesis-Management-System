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
        'ThoiGianBatDauSuaDiem',
        'ThoiGianKetThucSuaDiem',
        'TrangThaiMoKhoaSuaDiem',
        'LyDoMoKhoaSuaDiem',
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
            'ThoiGianBatDauSuaDiem' => 'datetime',
            'ThoiGianKetThucSuaDiem' => 'datetime',
            'TrangThaiMoKhoaSuaDiem' => 'boolean',
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

        // 3. Đã kết thúc: công bố quyết định hội đồng không phải công bố điểm.
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

        // Existing saved ballots may be edited until result publication.
        if ($hoSo && $giangVien && PhieuChamDiem::where('MaHoiDong', $this->MaHoiDong)
            ->where('MaHoSo', $hoSo->MaHoSo)->where('MaGV', $giangVien->MaGV)
            ->whereIn('TrangThai', ['Lưu nháp', 'Đã hoàn thành'])->exists()) {
            return $this->canEditScore($giangVien, $hoSo, $reason);
        }
        if ($hoSo && $hoSo->hasPublishedResults() && !$this->TrangThaiMoKhoaSuaDiem) {
            $reason = 'Kết quả của hồ sơ đã công bố. Không thể lưu hoặc chỉnh sửa điểm.';
            return false;
        }
        // Đã kết thúc: kiểm tra hạn nhập phiếu mới, không dùng công bố quyết định hội đồng.
        if ($tt === 'DaKetThuc') {
            $deadline = $this->han_cham_diem;
            $hasEditableBallot = !$hoSo && $giangVien && $this->hoSoBaoVes()->get()->contains(function ($candidate) use ($giangVien) {
                return PhieuChamDiem::where('MaHoiDong', $this->MaHoiDong)->where('MaHoSo', $candidate->MaHoSo)
                    ->where('MaGV', $giangVien->MaGV)->whereIn('TrangThai', ['Lưu nháp', 'Đã hoàn thành'])->exists()
                    && $this->canEditScore($giangVien, $candidate);
            });
            if ($deadline && $now->gt($deadline) && !$hasEditableBallot) {
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
            if ($hoSo->hasPublishedResults() && !$this->TrangThaiMoKhoaSuaDiem) {
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
                    // Kiểm tra xem giảng viên có còn quyền chỉnh sửa điểm trong thời hạn hay không
                    if (!$this->canEditScore($giangVien, $hoSo, $editReason)) {
                        $reason = $editReason ?: 'Phiếu chấm điểm của Thầy/Cô đã được xác nhận và khóa.';
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Kiểm tra quyền chỉnh sửa điểm của Giảng viên hội đồng theo quy chế nghiêm ngặt:
     * 1. Quyền chỉnh sửa: Mỗi GV chỉ được sửa điểm trong phiếu của chính mình.
     * 2. Phiếu đã lưu được chỉnh sửa từ khi mở chấm đến trước công bố kết quả.
     *    Phiếu mới và luồng mở khóa đặc biệt tiếp tục tuân thủ thời hạn tương ứng.
     * 3. Khi công bố điểm: Khi đã công bố điểm chính thức, tự động khóa quyền sửa điểm của cả 3 GV.
     *    Chỉ Quản trị viên/Trưởng khoa được mở khóa khi xử lý sai sót và phải ghi nhận lý do.
     */
    public function canEditScore($userOrGiangVien, ?HoSoBaoVe $hoSo = null, ?string &$reason = null): bool
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
                $reason = 'Thầy/Cô không thuộc Hội đồng bảo vệ này nên không có quyền chỉnh sửa điểm.';
                return false;
            }
        }

        // 2. Kiểm tra CÔNG BỐ ĐIỂM CHÍNH THỨC (Yêu cầu 3)
        // Khi quản trị viên công bố điểm chính thức, hệ thống tự động khóa quyền chỉnh sửa điểm của cả 3 giảng viên.
        // Sau khi công bố, giảng viên không được phép thay đổi bất kỳ điểm nào trừ khi Quản trị viên mở khóa (TrangThaiMoKhoaSuaDiem = true).
        // LƯU Ý: TrangThaiCongBo là trạng thái công bố thành lập Hội đồng, KHÔNG PHẢI công bố điểm!
        // Chỉ coi là đã công bố điểm khi Trưởng khoa đã xác nhận công bố kết quả điểm bảo vệ cho đề tài này.
        $isCongBoDiem = false;
        if ($hoSo && $hoSo->hasPublishedResults()) {
            $isCongBoDiem = true;
        } elseif (!$hoSo && $this->relationLoaded('hoSoBaoVes') && $this->hoSoBaoVes->isNotEmpty() && $this->hoSoBaoVes->every(fn($h) => in_array($h->TrangThaiKetQua, ['DaCongBo', 'CoYeuCauChinhSua', 'DaHoanThanh']))) {
            $isCongBoDiem = true;
        }

        if ($isCongBoDiem && !$this->TrangThaiMoKhoaSuaDiem) {
            $reason = 'Điểm chính thức đã được Quản trị viên/Trưởng khoa công bố. Toàn bộ điểm số đã bị khóa và không được phép chỉnh sửa.';
            return false;
        }

        if ($hoSo && $hoSo->MaHoiDong !== $this->MaHoiDong) {
            $reason = 'Hồ sơ bảo vệ không thuộc Hội đồng bảo vệ này.';
            return false;
        }
        if ($this->trang_thai_thoi_gian === 'SapDienRa') {
            $reason = 'Hội đồng chưa đến thời gian chấm điểm.';
            return false;
        }
        if ($hoSo && !$isCongBoDiem && $giangVien && PhieuChamDiem::where('MaHoiDong', $this->MaHoiDong)
            ->where('MaHoSo', $hoSo->MaHoSo)->where('MaGV', $giangVien->MaGV)
            ->whereIn('TrangThai', ['Lưu nháp', 'Đã hoàn thành'])->exists()) {
            return true;
        }
        // 3. Giữ thời hạn cho các trường hợp còn lại và quy trình mở khóa đặc biệt.
        // Quản trị viên thiết lập thời gian bắt đầu và kết thúc chỉnh sửa điểm.
        if ($this->ThoiGianBatDauSuaDiem && $now->lt($this->ThoiGianBatDauSuaDiem) && !$this->TrangThaiMoKhoaSuaDiem) {
            $reason = 'Chưa đến thời hạn chỉnh sửa điểm cho Hội đồng này (Bắt đầu từ: ' . $this->ThoiGianBatDauSuaDiem->format('d/m/Y H:i') . ').';
            return false;
        }

        if ($this->ThoiGianKetThucSuaDiem && $now->gt($this->ThoiGianKetThucSuaDiem) && !$this->TrangThaiMoKhoaSuaDiem) {
            $reason = 'Đã hết thời hạn chỉnh sửa điểm cho Hội đồng này (Thời hạn kết thúc: ' . $this->ThoiGianKetThucSuaDiem->format('d/m/Y H:i') . '). Hệ thống đã tự động khóa chức năng sửa điểm.';
            return false;
        }

        // Fallback thời hạn: Nếu chưa thiết lập thời hạn sửa điểm riêng:
        // Nếu Hội đồng chưa diễn ra:
        if (!$this->ThoiGianKetThucSuaDiem && $this->trang_thai_thoi_gian === 'SapDienRa' && !$this->TrangThaiMoKhoaSuaDiem) {
            $start = $this->thoi_diem_bat_dau;
            $openTime = $start ? $start->format('H:i \n\g\à\y d/m/Y') : 'thời gian quy định';
            $reason = "Hội đồng chưa đến thời gian chấm điểm / sửa điểm. Mở từ {$openTime}.";
            return false;
        }

        // Nếu Hội đồng đã kết thúc và quá hạn chấm điểm mặc định:
        if (!$this->ThoiGianKetThucSuaDiem && $this->trang_thai_thoi_gian === 'DaKetThuc' && !$this->TrangThaiMoKhoaSuaDiem) {
            $deadline = $this->han_cham_diem;
            if ($deadline && $now->gt($deadline)) {
                $reason = "Đã hết thời hạn chỉnh sửa điểm theo quy chế Hội đồng (Hạn chót: {$deadline->format('d/m/Y H:i')}). Hệ thống đã khóa chức năng sửa điểm.";
                return false;
            }
        }

        // 4. Kiểm tra hồ sơ bảo vệ
        if ($hoSo && $hoSo->MaHoiDong !== $this->MaHoiDong) {
            $reason = 'Hồ sơ bảo vệ không thuộc Hội đồng bảo vệ này.';
            return false;
        }

        return true;
    }

    public function lichSuChinhSuaDiems()
    {
        return $this->hasMany(LichSuChinhSuaDiem::class, 'MaHoiDong', 'MaHoiDong');
    }

    /**
     * Thành viên giữ vai trò Chủ tịch Hội đồng
     */
    public function getChuTichAttribute(): ?ThanhVienHoiDong
    {
        return $this->thanhViens->first(fn($tv) => \App\Enums\VaiTroHoiDong::isChuTich($tv->VaiTro));
    }

    /**
     * Thành viên giữ vai trò Thư ký Hội đồng
     */
    public function getThuKyAttribute(): ?ThanhVienHoiDong
    {
        return $this->thanhViens->first(fn($tv) => \App\Enums\VaiTroHoiDong::isThuKy($tv->VaiTro));
    }

    /**
     * Thành viên giữ vai trò Phản biện Hội đồng
     */
    public function getPhanBienAttribute(): ?ThanhVienHoiDong
    {
        return $this->thanhViens->first(fn($tv) => \App\Enums\VaiTroHoiDong::isPhanBien($tv->VaiTro));
    }

    /**
     * Danh sách thành viên khác (Ủy viên & Phản biện, không bao gồm người đã là Chủ tịch hoặc Thư ký)
     */
    public function getThanhVienKhacAttribute()
    {
        $chuTich = $this->chu_tich;
        $thuKy = $this->thu_ky;
        return $this->thanhViens->filter(function($tv) use ($chuTich, $thuKy) {
            if ($chuTich && $tv->MaGV === $chuTich->MaGV) return false;
            if ($thuKy && $tv->MaGV === $thuKy->MaGV) return false;
            return true;
        })->values();
    }

    /**
     * Danh sách thành viên sắp xếp theo thứ tự cố định: Chủ tịch -> Thư ký -> Phản biện -> Ủy viên
     */
    public function getSortedThanhViensAttribute()
    {
        return $this->thanhViens->sortBy('role_priority')->values();
    }
}
