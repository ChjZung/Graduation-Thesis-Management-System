<?php

namespace App\Services;

use App\Models\KeHoachKhoaLuan;
use App\Models\MocThoiGianKhoaLuan;
use App\Models\HocKy;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;

class PlanPhaseService
{
    /**
     * Lấy Kế hoạch Khóa luận đang kích hoạt (Công bố / Đang thực hiện) phù hợp nhất.
     */
    public static function getActivePlan(?string $maHocKy = null, ?string $maKhoa = null, ?string $maBoMon = null): ?KeHoachKhoaLuan
    {
        $query = KeHoachKhoaLuan::with('mocThoiGians')
            ->whereIn('TrangThai', ['ĐÃ CÔNG BỐ', 'ĐANG THỰC HIỆN', 'Đã công bố', 'Đang thực hiện']);

        if (!empty($maHocKy)) {
            $query->where('MaHocKy', $maHocKy);
        }

        if (!empty($maKhoa)) {
            $query->where(function ($q) use ($maKhoa) {
                $q->where('MaKhoa', $maKhoa)->orWhereNull('MaKhoa');
            });
        }

        if (!empty($maBoMon)) {
            $query->where(function ($q) use ($maBoMon) {
                $q->where('MaBoMon', $maBoMon)->orWhereNull('MaBoMon');
            });
        }

        $plan = $query->orderBy('created_at', 'desc')->first();

        if ($plan) {
            return $plan;
        }

        // Nếu chưa có plan cụ thể cho maHocKy, thử lấy bất kỳ plan nào đang có
        $fallbackPlan = KeHoachKhoaLuan::with('mocThoiGians')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($fallbackPlan) {
            return $fallbackPlan;
        }

        // Tự động khởi tạo Kế hoạch chuẩn nếu bảng đang rỗng
        return self::ensureDefaultPlanExists($maHocKy);
    }

    /**
     * Tự động khởi tạo Kế hoạch mặc định theo thông báo của Khoa nếu hệ thống chưa có
     */
    public static function ensureDefaultPlanExists(?string $maHocKy = null): KeHoachKhoaLuan
    {
        if (empty($maHocKy)) {
            $activeHocKy = HocKy::where('TrangThai', 'Đang diễn ra')->first() 
                ?? HocKy::orderBy('MaHocKy', 'desc')->first();
            $maHocKy = $activeHocKy ? $activeHocKy->MaHocKy : 'HK2425_1';
        }

        $maKH = 'KH_' . date('Y') . '_' . Str::upper(Str::random(4));

        $keHoach = KeHoachKhoaLuan::create([
            'MakeHoach'   => $maKH,
            'TenKeHoach'  => 'Kế hoạch Khóa luận HK1 2026-2027 (Theo Thông báo số 27/TB-KCNTT)',
            'MaHocKy'     => $maHocKy,
            'NoiDung'     => 'Kế hoạch thực hiện Khóa luận cử nhân và Khóa luận tốt nghiệp HK1 2026-2027 theo chuẩn Khoa CNTT - HUIT',
            'TrangThai'   => 'Đang thực hiện',
            'NgayTao'     => '2026-08-10',
            'MaGVu'       => 'GVU01',
        ]);

        $defaultPhases = self::getDefaultPhases('2026-08-10');
        foreach ($defaultPhases as $idx => $m) {
            $maMoc = 'MOC_' . substr($maKH, -4) . '_' . str_pad($idx + 1, 2, '0', STR_PAD_LEFT);
            MocThoiGianKhoaLuan::create([
                'MaMoc'       => $maMoc,
                'TenMoc'      => $m['TenMoc'],
                'NgayBatDau'  => $m['NgayBatDau'],
                'NgayKetThuc' => $m['NgayKetThuc'],
                'MoTa'        => $m['MoTa'] . " [{$m['LoaiGiaiDoan']}]",
                'MakeHoach'   => $keHoach->MakeHoach,
            ]);
        }

        return $keHoach->load('mocThoiGians');
    }

    /**
     * Xác định Giai đoạn / Mốc thời gian hiện tại của kế hoạch dựa vào ngày giờ hiện tại.
     */
    public static function getCurrentPhase(?KeHoachKhoaLuan $plan = null): ?MocThoiGianKhoaLuan
    {
        $plan = $plan ?? self::getActivePlan();
        if (!$plan || !$plan->mocThoiGians || $plan->mocThoiGians->isEmpty()) return null;

        $now = Carbon::now();
        $today = $now->format('Y-m-d');

        // Tìm mốc thời gian khớp ngày giờ
        $currentMoc = $plan->mocThoiGians->first(function ($moc) use ($today) {
            return $today >= $moc->NgayBatDau && $today <= $moc->NgayKetThuc;
        });

        if ($currentMoc) {
            return $currentMoc;
        }

        return $plan->mocThoiGians->first(function ($moc) use ($today) {
            return $moc->NgayKetThuc >= $today;
        }) ?? $plan->mocThoiGians->last();
    }

    /**
     * Kiểm tra một loại giai đoạn cụ thể có đang MỞ hay không theo ngày và giờ.
     */
    public static function isPhaseOpen(string $loaiGiaiDoan, ?KeHoachKhoaLuan $plan = null, $currentTime = null): bool
    {
        $plan = $plan ?? self::getActivePlan();
        if (!$plan || !$plan->mocThoiGians) return false;

        $now = $currentTime ? Carbon::parse($currentTime) : Carbon::now();
        $currentDate = $now->format('Y-m-d');
        $currentTimeStr = $now->format('H:i:s');

        // Tìm mốc theo mã giai đoạn
        $moc = $plan->mocThoiGians->first(function ($m) use ($loaiGiaiDoan) {
            if (!empty($m->LoaiGiaiDoan) && $m->LoaiGiaiDoan === $loaiGiaiDoan) return true;
            if (!empty($m->MoTa) && str_contains($m->MoTa, "[{$loaiGiaiDoan}]")) return true;
            return str_contains(mb_strtoupper($m->TenMoc), mb_strtoupper($loaiGiaiDoan));
        });

        if (!$moc) return false;

        // Ràng buộc thời gian chuyên biệt từng mốc:
        switch ($loaiGiaiDoan) {
            case 'TAO_NHOM':
                // Thời gian tạo nhóm: từ 00:00:00 của NgayBatDau đến 23:59:00 của NgayKetThuc (ví dụ 10/08/2026)
                $startLimit = Carbon::parse($moc->NgayBatDau . ' 00:00:00');
                $endLimit   = Carbon::parse($moc->NgayKetThuc . ' 23:59:00');
                return ($now >= $startLimit && $now <= $endLimit);

            case 'DANG_KY_DE_TAI':
                // Khung giờ 08:00 - 20:00 trong ngày đăng ký (11/08/2026)
                if ($currentDate < $moc->NgayBatDau || $currentDate > $moc->NgayKetThuc) {
                    return false;
                }
                return ($currentTimeStr >= '08:00:00' && $currentTimeStr <= '20:00:00');

            case 'XU_LY_NGOAI_LE':
            case 'DANG_KY_BO_SUNG':
                // Khung giờ 08:00 - 16:00 trong ngày ngoại lệ / bổ sung (12/08/2026)
                if ($currentDate < $moc->NgayBatDau || $currentDate > $moc->NgayKetThuc) {
                    return false;
                }
                return ($currentTimeStr >= '08:00:00' && $currentTimeStr <= '16:00:00');

            default:
                $startLimit = Carbon::parse($moc->NgayBatDau . ' 00:00:00');
                $endLimit   = Carbon::parse($moc->NgayKetThuc . ' 23:59:59');
                return ($now >= $startLimit && $now <= $endLimit);
        }
    }

    /**
     * Xác định trạng thái của giai đoạn Tạo & Ghép Nhóm (Chưa mở, Đang mở, Đã đóng)
     */
    public static function getGroupPhaseState($planOrHocKy = null, $currentTime = null): array
    {
        if (is_string($planOrHocKy)) {
            $maHocKy = $planOrHocKy;
            $plan = KeHoachKhoaLuan::with('mocThoiGians')->where('MaHocKy', $maHocKy)->orderBy('created_at', 'desc')->first();
        } else {
            $plan = $planOrHocKy ?? self::getActivePlan();
            $maHocKy = $plan?->MaHocKy;
        }

        $now = $currentTime ? Carbon::parse($currentTime) : Carbon::now();

        // 1. Tìm mốc từ relation mocThoiGians
        $mocTaoNhom = null;
        if ($plan && $plan->relationLoaded('mocThoiGians')) {
            $mocTaoNhom = $plan->mocThoiGians->first(fn($m) => 
                $m->LoaiGiaiDoan === 'TAO_NHOM' || str_contains($m->MoTa ?? '', '[TAO_NHOM]') || str_contains(mb_strtoupper($m->TenMoc), 'TẠO NHÓM')
            );
        }

        // 2. Query trực tiếp từ DB theo MakeHoach hoặc MaHocKy nếu chưa có
        if (!$mocTaoNhom && $plan) {
            $mocTaoNhom = MocThoiGianKhoaLuan::where('MakeHoach', $plan->MakeHoach)
                ->where(function($q) {
                    $q->where('TenMoc', 'LIKE', '%tạo nhóm%')
                      ->orWhere('TenMoc', 'LIKE', '%ghép nhóm%')
                      ->orWhere('MoTa', 'LIKE', '%[TAO_NHOM]%');
                })->first();
        }

        if (!$mocTaoNhom && $maHocKy) {
            $mocTaoNhom = MocThoiGianKhoaLuan::whereHas('keHoach', fn($kq) => $kq->where('MaHocKy', $maHocKy))
                ->where(function($q) {
                    $q->where('TenMoc', 'LIKE', '%tạo nhóm%')
                      ->orWhere('TenMoc', 'LIKE', '%ghép nhóm%')
                      ->orWhere('MoTa', 'LIKE', '%[TAO_NHOM]%');
                })->first();
        }

        if (!$mocTaoNhom) {
            // Không có cấu hình mốc tạo nhóm trong DB -> Đóng cổng
            return [
                'code'          => 'CHUA_CAU_HINH',
                'label'         => 'Chưa Mở Cổng Tạo Nhóm',
                'badge'         => 'bg-secondary',
                'is_open'       => false,
                'start_date'    => null,
                'end_date'      => null,
                'message'       => 'Hiện chưa có mốc thời gian tạo nhóm được công bố trong Kế hoạch khóa luận của học kỳ này.',
            ];
        }

        $startDate = Carbon::parse($mocTaoNhom->NgayBatDau . ' 00:00:00');
        $endDate   = Carbon::parse($mocTaoNhom->NgayKetThuc . ' 23:59:00');

        if ($now < $startDate) {
            return [
                'code'          => 'CHUA_MO',
                'label'         => 'Chưa Mở Cổng Tạo Nhóm',
                'badge'         => 'bg-secondary',
                'is_open'       => false,
                'start_date'    => $startDate,
                'end_date'      => $endDate,
                'message'       => 'Cổng tạo nhóm khóa luận chưa mở. Thời gian mở từ: ' . $startDate->format('d/m/Y') . ' đến 23:59 ngày ' . $endDate->format('d/m/Y') . '.',
            ];
        }

        if ($now >= $startDate && $now <= $endDate) {
            return [
                'code'          => 'DANG_MO',
                'label'         => 'Đang Mở Tạo Nhóm',
                'badge'         => 'bg-success',
                'is_open'       => true,
                'start_date'    => $startDate,
                'end_date'      => $endDate,
                'message'       => 'Cổng tạo và ghép nhóm đang mở! Hạn chót hoàn thiện nhóm 3 thành viên: 23:59 ngày ' . $endDate->format('d/m/Y') . '.',
            ];
        }

        return [
            'code'          => 'DA_DONG',
            'label'         => 'Đã Khóa Tạo Nhóm',
            'badge'         => 'bg-danger',
            'is_open'       => false,
            'start_date'    => $startDate,
            'end_date'      => $endDate,
            'message'       => 'Đã hết thời hạn tạo nhóm và ghép thành viên theo Kế hoạch khóa luận (Hạn chót: 23:59 ngày ' . $endDate->format('d/m/Y') . '). Hệ thống đã chốt danh sách nhóm và không cho phép tạo nhóm mới!',
        ];
    }

    /**
     * Xác định trạng thái đăng ký đề tài của học kỳ (Chưa mở, Chính thức, Tạm khóa, Bổ sung, Đã kết thúc)
     */
    public static function getRegistrationState(?KeHoachKhoaLuan $plan = null, $currentTime = null): array
    {
        $plan = $plan ?? self::getActivePlan();
        $now = $currentTime ? Carbon::parse($currentTime) : Carbon::now();

        $mocChinhThuc = $plan ? $plan->mocThoiGians->first(fn($m) => 
            $m->LoaiGiaiDoan === 'DANG_KY_DE_TAI' || str_contains($m->MoTa ?? '', '[DANG_KY_DE_TAI]')
        ) : null;

        $mocBoSung = $plan ? $plan->mocThoiGians->first(fn($m) => 
            $m->LoaiGiaiDoan === 'XU_LY_NGOAI_LE' || str_contains($m->MoTa ?? '', '[XU_LY_NGOAI_LE]')
        ) : null;

        $startDateOfficial = $mocChinhThuc ? Carbon::parse($mocChinhThuc->NgayBatDau . ' 08:00:00') : Carbon::parse('2026-08-11 08:00:00');
        $endDateOfficial   = $mocChinhThuc ? Carbon::parse($mocChinhThuc->NgayKetThuc . ' 20:00:00') : Carbon::parse('2026-08-11 20:00:00');

        $startDateSupp = $mocBoSung ? Carbon::parse($mocBoSung->NgayBatDau . ' 08:00:00') : Carbon::parse('2026-08-12 08:00:00');
        $endDateSupp   = $mocBoSung ? Carbon::parse($mocBoSung->NgayKetThuc . ' 16:00:00') : Carbon::parse('2026-08-12 16:00:00');

        // 1. Chưa mở đăng ký chính thức
        if ($now < $startDateOfficial) {
            return [
                'code'        => 'CHUA_MO',
                'label'       => 'Chưa Mở Đăng Ký',
                'badge'       => 'bg-secondary',
                'can_register'=> false,
                'is_supplementary' => false,
                'message'     => 'Cổng đăng ký đề tài sẽ chính thức mở vào lúc 08:00 ngày ' . $startDateOfficial->format('d/m/Y') . '.',
            ];
        }

        // 2. Đang trong đợt đăng ký chính thức
        if ($now >= $startDateOfficial && $now <= $endDateOfficial) {
            return [
                'code'        => 'DANG_KY_CHINH_THUC',
                'label'       => 'Đang Mở Đăng Ký Chính Thức',
                'badge'       => 'bg-success',
                'can_register'=> true,
                'is_supplementary' => false,
                'message'     => 'Đợt đăng ký chính thức đang mở (08:00 - 20:00 ngày ' . $startDateOfficial->format('d/m/Y') . '). Nhóm đủ 3 thành viên, Nhóm trưởng nhanh chóng đăng ký đề tài!',
            ];
        }

        // 3. Khóa tạm thời sau đợt chính thức, trước đợt bổ sung
        if ($now > $endDateOfficial && $now < $startDateSupp) {
            return [
                'code'        => 'KHOA_TAM_THOI',
                'label'       => 'Khóa Tạm Thời (Chờ Bổ Sung)',
                'badge'       => 'bg-danger',
                'can_register'=> false,
                'is_supplementary' => false,
                'message'     => 'Đã hết thời hạn đăng ký chính thức (20:00). Cổng đăng ký tạm khóa để rà soát dữ liệu. Đợt đăng ký bổ sung sẽ mở vào lúc 08:00 ngày ' . $startDateSupp->format('d/m/Y') . '.',
            ];
        }

        // 4. Đang trong đợt đăng ký bổ sung / Ngoại lệ
        if ($now >= $startDateSupp && $now <= $endDateSupp) {
            return [
                'code'        => 'DANG_KY_BO_SUNG',
                'label'       => 'Đang Mở Đăng Ký Bổ Sung',
                'badge'       => 'bg-warning text-dark',
                'can_register'=> true,
                'is_supplementary' => true,
                'message'     => 'Đợt đăng ký bổ sung đang mở (08:00 - 16:00 ngày ' . $startDateSupp->format('d/m/Y') . ') dành riêng cho các nhóm đủ 3 thành viên CHƯA CÓ ĐỀ TÀI.',
            ];
        }

        // 5. Đã kết thúc toàn bộ đợt đăng ký
        return [
            'code'        => 'DA_KET_THUC',
            'label'       => 'Đã Đóng Cổng Đăng Ký',
            'badge'       => 'bg-dark',
            'can_register'=> false,
            'is_supplementary' => false,
            'message'     => 'Đã kết thúc thời hạn đăng ký đề tài. Khoa đang tổng hợp danh sách để công bố chính thức.',
        ];
    }

    /**
     * Lấy trạng thái hiển thị trực quan của mốc thời gian (Chưa bắt đầu, Đang mở, Đã kết thúc).
     */
    public static function getPhaseStatus(MocThoiGianKhoaLuan $moc, $currentTime = null): array
    {
        $now = $currentTime ? Carbon::parse($currentTime) : Carbon::now();
        $today = $now->format('Y-m-d');

        $isOpen = false;
        if (!empty($moc->LoaiGiaiDoan)) {
            $isOpen = self::isPhaseOpen($moc->LoaiGiaiDoan, $moc->keHoach, $now);
        } else {
            $isOpen = ($today >= $moc->NgayBatDau && $today <= $moc->NgayKetThuc);
        }

        if ($isOpen) {
            $daysLeft = Carbon::parse($today)->diffInDays(Carbon::parse($moc->NgayKetThuc));
            return [
                'code'        => 'DANG_MO',
                'label'       => 'Đang mở',
                'badge'       => 'bg-success',
                'is_open'     => true,
                'days_left'   => $daysLeft,
            ];
        }

        if ($today < $moc->NgayBatDau || $now < Carbon::parse($moc->NgayBatDau . ' 00:00:00')) {
            return [
                'code'        => 'CHUA_BAT_DAU',
                'label'       => 'Chưa bắt đầu',
                'badge'       => 'bg-secondary',
                'is_open'     => false,
                'days_left'   => Carbon::parse($today)->diffInDays(Carbon::parse($moc->NgayBatDau)),
            ];
        }

        return [
            'code'        => 'DA_KET_THUC',
            'label'       => 'Đã kết thúc',
            'badge'       => 'bg-dark opacity-75',
            'is_open'     => false,
            'days_left'   => 0,
        ];
    }

    /**
     * Chặn thao tác bằng Exception nếu giai đoạn tương ứng đã ĐÓNG.
     */
    public static function assertPhaseOpen(string $loaiGiaiDoan, ?KeHoachKhoaLuan $plan = null, ?string $customMessage = null): void
    {
        $plan = $plan ?? self::getActivePlan();
        if (!$plan) {
            throw new Exception("Hiện chưa có Kế hoạch Khóa luận nào được công bố trong hệ thống.");
        }

        if (!self::isPhaseOpen($loaiGiaiDoan, $plan)) {
            $moc = $plan->mocThoiGians ? $plan->mocThoiGians->firstWhere('LoaiGiaiDoan', $loaiGiaiDoan) : null;
            $tenMoc = $moc ? $moc->TenMoc : $loaiGiaiDoan;
            $thoiGian = $moc ? " ({$moc->NgayBatDau} đến {$moc->NgayKetThuc})" : '';

            $msg = $customMessage ?? "Giai đoạn '{$tenMoc}'{$thoiGian} hiện không mở. Thao tác bị tạm khóa theo mốc thời gian kế hoạch.";
            throw new Exception($msg);
        }
    }

    /**
     * Danh sách các mốc chuẩn định nghĩa sẵn cho kế hoạch theo đúng thông báo của Khoa CNTT - HUIT.
     * Lưu ý: Báo cáo tiến độ là chức năng riêng biệt, không thêm vào các mốc kế hoạch chung.
     */
    public static function getDefaultPhases(string $startDateStr = '2026-08-10'): array
    {
        return [
            [
                'LoaiGiaiDoan'    => 'TAO_NHOM',
                'ThuTu'           => 1,
                'TenMoc'          => '1. Sinh viên tạo nhóm trên phần mềm HUIT-STUDENT',
                'DoiTuongThucHien'=> 'Sinh viên',
                'NgayBatDau'      => '2026-08-10',
                'NgayKetThuc'     => '2026-08-10',
                'MoTa'            => 'Sinh viên tạo nhóm trên phần mềm HUIT-STUDENT (mỗi nhóm 3 SV/1 đề tài, đúng chuyên ngành). Khung giờ: 08:00 - 23:59.',
                'BatBuoc'         => true,
            ],
            [
                'LoaiGiaiDoan'    => 'DANG_KY_DE_TAI',
                'ThuTu'           => 2,
                'TenMoc'          => '2. Nhóm trưởng đăng ký đề tài theo đúng chuyên ngành',
                'DoiTuongThucHien'=> 'Nhóm trưởng',
                'NgayBatDau'      => '2026-08-11',
                'NgayKetThuc'     => '2026-08-11',
                'MoTa'            => 'Nhóm trưởng các nhóm đủ 3 SV thực hiện đăng ký đề tài đúng chuyên ngành. Khung giờ: 08:00 - 20:00.',
                'BatBuoc'         => true,
            ],
            [
                'LoaiGiaiDoan'    => 'XU_LY_NGOAI_LE',
                'ThuTu'           => 3,
                'TenMoc'          => '3. Xử lý các trường hợp ngoại lệ trực tiếp tại VPK',
                'DoiTuongThucHien'=> 'Giáo vụ Khoa',
                'NgayBatDau'      => '2026-08-12',
                'NgayKetThuc'     => '2026-08-12',
                'MoTa'            => 'Sinh viên đến trực tiếp VPK gặp Giáo vụ để hỗ trợ xử lý ngoại lệ (8h00 - 11h00 & 13h30 - 16h00).',
                'BatBuoc'         => true,
            ],
            [
                'LoaiGiaiDoan'    => 'CONG_BO_GVHD',
                'ThuTu'           => 4,
                'TenMoc'          => '4. Khoa thông báo chính thức danh sách SV và GVHD',
                'DoiTuongThucHien'=> 'Khoa CNTT',
                'NgayBatDau'      => '2026-08-14',
                'NgayKetThuc'     => '2026-08-14',
                'MoTa'            => 'Khoa tổng hợp danh sách SV đăng ký đề tài và GVHD, thông báo chính thức trên website và hệ thống.',
                'BatBuoc'         => true,
            ],
            [
                'LoaiGiaiDoan'    => 'LIEN_HE_GVHD',
                'ThuTu'           => 5,
                'TenMoc'          => '5. Sinh viên chủ động liên hệ GVHD qua email',
                'DoiTuongThucHien'=> 'Sinh viên & GVHD',
                'NgayBatDau'      => '2026-08-14',
                'NgayKetThuc'     => '2026-08-17',
                'MoTa'            => 'Sinh viên chủ động liên hệ GVHD qua email để trao đổi nội dung kế hoạch thực hiện đề tài.',
                'BatBuoc'         => true,
            ],
            [
                'LoaiGiaiDoan'    => 'THUC_HIEN',
                'ThuTu'           => 6,
                'TenMoc'          => '6. Thời gian thực hiện khóa luận (12 tuần)',
                'DoiTuongThucHien'=> 'Sinh viên & GVHD',
                'NgayBatDau'      => '2026-08-17',
                'NgayKetThuc'     => '2026-11-08',
                'MoTa'            => 'Thời gian thực hiện khóa luận 12 tuần: KLTN (Mã HP: 0101102534) và KLCN (Mã HP: 0101102008).',
                'BatBuoc'         => true,
            ],
            [
                'LoaiGiaiDoan'    => 'NOP_BAO_CAO',
                'ThuTu'           => 7,
                'TenMoc'          => '7. Sinh viên nộp báo cáo Khóa luận tốt nghiệp / cử nhân',
                'DoiTuongThucHien'=> 'Sinh viên',
                'NgayBatDau'      => '2026-11-11',
                'NgayKetThuc'     => '2026-11-11',
                'MoTa'            => 'SV nộp báo cáo Khóa luận cử nhân (0101102008) và Khóa luận tốt nghiệp (0101102534). Hình thức nộp Khoa thông báo sau.',
                'BatBuoc'         => true,
            ],
            [
                'LoaiGiaiDoan'    => 'THONG_BAO_HOI_DONG',
                'ThuTu'           => 8,
                'TenMoc'          => '8. Thông báo lịch làm việc của các Hội đồng bảo vệ KLCN, KLTN',
                'DoiTuongThucHien'=> 'Khoa CNTT',
                'NgayBatDau'      => '2026-11-13',
                'NgayKetThuc'     => '2026-11-13',
                'MoTa'            => 'Thông báo lịch làm việc của các hội đồng bảo vệ KLCN, KLTN đến SV trên bảng tin website Khoa.',
                'BatBuoc'         => true,
            ],
            [
                'LoaiGiaiDoan'    => 'BAO_VE',
                'ThuTu'           => 9,
                'TenMoc'          => '9. Tổ chức các buổi bảo vệ khóa luận trước Hội đồng chấm',
                'DoiTuongThucHien'=> 'Khoa & Hội đồng',
                'NgayBatDau'      => '2026-11-18',
                'NgayKetThuc'     => '2026-11-28',
                'MoTa'            => 'Tổ chức các buổi bảo vệ khóa luận trước Hội đồng chấm KLCN, KLTN.',
                'BatBuoc'         => true,
            ],
        ];
    }

    /**
     * Danh sách 8 Quy định & Tiêu chuẩn chuẩn trích xuất từ văn bản kế hoạch khóa luận
     */
    public static function getDefaultRegulations(): array
    {
        return [
            [
                'TenQuyDinh' => 'Số tín chỉ tích lũy tối thiểu làm KLTN',
                'GiaTri'     => '115 Tín chỉ',
                'MoTa'       => 'Sinh viên phải tích lũy tối thiểu 115 tín chỉ và không nợ các môn điều kiện tiên quyết.',
            ],
            [
                'TenQuyDinh' => 'Điểm trung bình tích lũy tối thiểu (GPA)',
                'GiaTri'     => '2.0 GPA',
                'MoTa'       => 'Điểm trung bình tích lũy thang điểm 4.0 đạt từ 2.0 trở lên tại thời điểm xét duyệt.',
            ],
            [
                'TenQuyDinh' => 'Số lượng sinh viên tối đa trong một nhóm',
                'GiaTri'     => '2 - 3 Sinh viên',
                'MoTa'       => 'Mỗi nhóm khóa luận gồm 2 đến 3 sinh viên (trừ trường hợp đặc biệt được Trưởng khoa duyệt).',
            ],
            [
                'TenQuyDinh' => 'Định mức đề tài tối đa một giảng viên hướng dẫn',
                'GiaTri'     => 'Tối đa 5 Đề tài / GV',
                'MoTa'       => 'Mỗi giảng viên hướng dẫn tối đa 5 đề tài/nhóm trong một học kỳ để đảm bảo chất lượng hướng dẫn.',
            ],
            [
                'TenQuyDinh' => 'Ngưỡng trùng lặp kiểm tra Turnitin tối đa',
                'GiaTri'     => '<= 20%',
                'MoTa'       => 'Báo cáo toàn văn quét qua hệ thống Turnitin có độ trùng lặp không được vượt quá 20%.',
            ],
            [
                'TenQuyDinh' => 'Điểm tổng kết tối thiểu để đạt Khóa luận',
                'GiaTri'     => '>= 5.0 Điểm',
                'MoTa'       => 'Điểm tổng kết bảo vệ theo trọng số (GVHD 30%, GVPB 30%, Hội đồng 40%) phải đạt từ 5.0 trở lên.',
            ],
            [
                'TenQuyDinh' => 'Thời gian thực hiện khóa luận tốt nghiệp',
                'GiaTri'     => '12 Tuần',
                'MoTa'       => 'Thời gian từ khi công bố đề tài chính thức đến khi nộp báo cáo hoàn chỉnh bảo vệ theo Thông báo số 27/TB-KCNTT.',
            ],
            [
                'TenQuyDinh' => 'Yêu cầu hồ sơ và sản phẩm nộp bảo vệ',
                'GiaTri'     => '03 Cuốn báo cáo + Source code + Slide',
                'MoTa'       => 'Sinh viên nộp cuốn báo cáo đúng format, mã nguồn hoàn chỉnh và slide trình bày trước ngày bảo vệ.',
            ],
        ];
    }
}
