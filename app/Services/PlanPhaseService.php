<?php

namespace App\Services;

use App\Models\KeHoachKhoaLuan;
use App\Models\MocThoiGianKhoaLuan;
use Carbon\Carbon;
use Exception;

class PlanPhaseService
{
    /**
     * Lấy Kế hoạch Khóa luận đang kích hoạt (Công bố / Đang thực hiện) phù hợp nhất.
     */
    public static function getActivePlan(?string $maKhoa = null, ?string $maBoMon = null): ?KeHoachKhoaLuan
    {
        $query = KeHoachKhoaLuan::with('mocThoiGians')
            ->whereIn('TrangThai', ['ĐÃ CÔNG BỐ', 'ĐANG THỰC HIỆN', 'Đã công bố']);

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

        $plans = $query->orderBy('created_at', 'desc')->get();

        $today = Carbon::today()->format('Y-m-d');
        foreach ($plans as $plan) {
            if ($plan->NgayBatDau && $plan->NgayKetThuc) {
                if ($today >= $plan->NgayBatDau && $today <= $plan->NgayKetThuc) {
                    return $plan;
                }
            }
        }

        return $plans->first();
    }

    /**
     * Xác định Giai đoạn / Mốc thời gian hiện tại của kế hoạch dựa vào ngày hiện tại.
     */
    public static function getCurrentPhase(?KeHoachKhoaLuan $plan = null): ?MocThoiGianKhoaLuan
    {
        $plan = $plan ?? self::getActivePlan();
        if (!$plan) return null;

        $today = Carbon::today()->format('Y-m-d');

        // Tìm mốc thời gian trùng ngày hiện tại
        $currentMoc = $plan->mocThoiGians->first(function ($moc) use ($today) {
            return $today >= $moc->NgayBatDau && $today <= $moc->NgayKetThuc;
        });

        if ($currentMoc) {
            return $currentMoc;
        }

        // Nếu không có mốc khớp chính xác, trả về mốc gần nhất chưa kết thúc
        return $plan->mocThoiGians->first(function ($moc) use ($today) {
            return $moc->NgayKetThuc >= $today;
        }) ?? $plan->mocThoiGians->last();
    }

    /**
     * Kiểm tra một loại giai đoạn cụ thể có đang MỞ hay không.
     */
    public static function isPhaseOpen(string $loaiGiaiDoan, ?KeHoachKhoaLuan $plan = null): bool
    {
        $plan = $plan ?? self::getActivePlan();
        if (!$plan) return false;

        $today = Carbon::today()->format('Y-m-d');

        $moc = $plan->mocThoiGians->firstWhere('LoaiGiaiDoan', $loaiGiaiDoan);
        if (!$moc) {
            // Fallback match tên mốc nếu chưa gán LoaiGiaiDoan
            $moc = $plan->mocThoiGians->first(function ($m) use ($loaiGiaiDoan) {
                return str_contains(mb_strtoupper($m->TenMoc), mb_strtoupper($loaiGiaiDoan));
            });
        }

        if (!$moc) return false;

        return ($today >= $moc->NgayBatDau && $today <= $moc->NgayKetThuc);
    }

    /**
     * Lấy trạng thái hiển thị trực quan của mốc thời gian (Chưa bắt đầu, Đang mở, Đã kết thúc).
     */
    public static function getPhaseStatus(MocThoiGianKhoaLuan $moc): array
    {
        $today = Carbon::today()->format('Y-m-d');

        if ($today < $moc->NgayBatDau) {
            return [
                'code'        => 'CHUA_BAT_DAU',
                'label'       => 'Chưa bắt đầu',
                'badge'       => 'bg-secondary',
                'is_open'     => false,
                'days_left'   => Carbon::parse($today)->diffInDays(Carbon::parse($moc->NgayBatDau)),
            ];
        } elseif ($today >= $moc->NgayBatDau && $today <= $moc->NgayKetThuc) {
            return [
                'code'        => 'DANG_MO',
                'label'       => 'Đang mở',
                'badge'       => 'bg-success',
                'is_open'     => true,
                'days_left'   => Carbon::parse($today)->diffInDays(Carbon::parse($moc->NgayKetThuc)),
            ];
        } else {
            return [
                'code'        => 'DA_KET_THUC',
                'label'       => 'Đã kết thúc',
                'badge'       => 'bg-dark opacity-75',
                'is_open'     => false,
                'days_left'   => 0,
            ];
        }
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
            $moc = $plan->mocThoiGians->firstWhere('LoaiGiaiDoan', $loaiGiaiDoan);
            $tenMoc = $moc ? $moc->TenMoc : $loaiGiaiDoan;
            $thoiGian = $moc ? " ({$moc->NgayBatDau} đến {$moc->NgayKetThuc})" : '';

            $msg = $customMessage ?? "Giai đoạn '{$tenMoc}'{$thoiGian} hiện không mở. Thao tác bị tạm khóa theo mốc thời gian kế hoạch.";
            throw new Exception($msg);
        }
    }

    /**
     * Danh sách 12 mốc chuẩn định nghĩa sẵn cho kế hoạch.
     */
    public static function getDefaultPhases(string $startDateStr = '2026-09-01'): array
    {
        $start = Carbon::parse($startDateStr);

        return [
            [
                'LoaiGiaiDoan'    => 'CONG_BO_DE_TAI',
                'ThuTu'           => 1,
                'TenMoc'          => '1. Công bố danh sách đề tài',
                'DoiTuongThucHien'=> 'Khoa / Giáo vụ',
                'NgayBatDau'      => $start->copy()->format('Y-m-d'),
                'NgayKetThuc'     => $start->copy()->addDays(4)->format('Y-m-d'),
                'MoTa'            => 'Khoa & Bộ môn rà soát và công bố đề tài cho sinh viên đăng ký.',
                'BatBuoc'         => true,
            ],
            [
                'LoaiGiaiDoan'    => 'DANG_KY_DE_TAI',
                'ThuTu'           => 2,
                'TenMoc'          => '2. Sinh viên đăng ký đề tài',
                'DoiTuongThucHien'=> 'Sinh viên',
                'NgayBatDau'      => $start->copy()->addDays(5)->format('Y-m-d'),
                'NgayKetThuc'     => $start->copy()->addDays(11)->format('Y-m-d'),
                'MoTa'            => 'Sinh viên/Nhóm thực hiện đăng ký đề tài & đề xuất GVHD mong muốn.',
                'BatBuoc'         => true,
            ],
            [
                'LoaiGiaiDoan'    => 'XET_DUYET',
                'ThuTu'           => 3,
                'TenMoc'          => '3. Khoa xét duyệt đăng ký đề tài',
                'DoiTuongThucHien'=> 'Khoa / Ban quản trị',
                'NgayBatDau'      => $start->copy()->addDays(12)->format('Y-m-d'),
                'NgayKetThuc'     => $start->copy()->addDays(17)->format('Y-m-d'),
                'MoTa'            => 'Khoa phê duyệt hoặc yêu cầu chỉnh sửa các đơn đăng ký đề tài.',
                'BatBuoc'         => true,
            ],
            [
                'LoaiGiaiDoan'    => 'PHAN_CONG_GVHD',
                'ThuTu'           => 4,
                'TenMoc'          => '4. Phân công Giảng viên hướng dẫn',
                'DoiTuongThucHien'=> 'Khoa / Bộ môn',
                'NgayBatDau'      => $start->copy()->addDays(18)->format('Y-m-d'),
                'NgayKetThuc'     => $start->copy()->addDays(21)->format('Y-m-d'),
                'MoTa'            => 'Phân công chính thức GVHD cho các nhóm được duyệt.',
                'BatBuoc'         => true,
            ],
            [
                'LoaiGiaiDoan'    => 'THUC_HIEN',
                'ThuTu'           => 5,
                'TenMoc'          => '5. Bắt đầu thực hiện khóa luận & Hoàn thiện đề cương',
                'DoiTuongThucHien'=> 'Sinh viên & GVHD',
                'NgayBatDau'      => $start->copy()->addDays(22)->format('Y-m-d'),
                'NgayKetThuc'     => $start->copy()->addDays(35)->format('Y-m-d'),
                'MoTa'            => 'Nhóm bắt đầu thực hiện khóa luận, thống nhất đề cương và phân tích yêu cầu với GVHD.',
                'BatBuoc'         => true,
            ],
            [
                'LoaiGiaiDoan'    => 'BAO_CAO_TIEN_DO_1',
                'ThuTu'           => 6,
                'TenMoc'          => '6. Báo cáo tiến độ đợt 1 (Đề cương & Thiết kế CSDL)',
                'DoiTuongThucHien'=> 'Sinh viên & GVHD',
                'NgayBatDau'      => $start->copy()->addDays(36)->format('Y-m-d'),
                'NgayKetThuc'     => $start->copy()->addDays(40)->format('Y-m-d'),
                'MoTa'            => 'Nộp báo cáo tiến độ phân tích nghiệp vụ, đề cương chi tiết & mô hình CSDL.',
                'BatBuoc'         => true,
            ],
            [
                'LoaiGiaiDoan'    => 'BAO_CAO_TIEN_DO_2',
                'ThuTu'           => 7,
                'TenMoc'          => '7. Báo cáo tiến độ đợt 2 (Thiết kế hệ thống & Triển khai chức năng)',
                'DoiTuongThucHien'=> 'Sinh viên & GVHD',
                'NgayBatDau'      => $start->copy()->addDays(50)->format('Y-m-d'),
                'NgayKetThuc'     => $start->copy()->addDays(55)->format('Y-m-d'),
                'MoTa'            => 'Nộp báo cáo kiến trúc hệ thống, giao diện UI/UX & mã nguồn chức năng chính.',
                'BatBuoc'         => true,
            ],
            [
                'LoaiGiaiDoan'    => 'BAO_CAO_TIEN_DO_3',
                'ThuTu'           => 8,
                'TenMoc'          => '8. Báo cáo tiến độ đợt 3 (Kiểm thử chức năng & Hoàn thiện dự thảo)',
                'DoiTuongThucHien'=> 'Sinh viên & GVHD',
                'NgayBatDau'      => $start->copy()->addDays(68)->format('Y-m-d'),
                'NgayKetThuc'     => $start->copy()->addDays(72)->format('Y-m-d'),
                'MoTa'            => 'Kiểm thử toàn diện phần mềm, đo lường kết quả & hoàn thiện bản thảo toàn văn báo cáo.',
                'BatBuoc'         => true,
            ],
            [
                'LoaiGiaiDoan'    => 'DAO_VAN',
                'ThuTu'           => 9,
                'TenMoc'          => '9. Kiểm tra đạo văn (Quét Turnitin độ trùng lặp < 20%)',
                'DoiTuongThucHien'=> 'Sinh viên & Khoa',
                'NgayBatDau'      => $start->copy()->addDays(75)->format('Y-m-d'),
                'NgayKetThuc'     => $start->copy()->addDays(80)->format('Y-m-d'),
                'MoTa'            => 'Upload file cuốn toàn văn để kiểm tra Turnitin, đảm bảo tỷ lệ trùng lặp dưới 20%.',
                'BatBuoc'         => true,
            ],
            [
                'LoaiGiaiDoan'    => 'GVHD_XAC_NHAN',
                'ThuTu'           => 10,
                'TenMoc'          => '10. GVHD xác nhận đủ điều kiện bảo vệ',
                'DoiTuongThucHien'=> 'GVHD',
                'NgayBatDau'      => $start->copy()->addDays(81)->format('Y-m-d'),
                'NgayKetThuc'     => $start->copy()->addDays(85)->format('Y-m-d'),
                'MoTa'            => 'GVHD đánh giá tổng kết, chấm điểm hướng dẫn và ký xác nhận cho phép bảo vệ.',
                'BatBuoc'         => true,
            ],
            [
                'LoaiGiaiDoan'    => 'NOP_DO_AN',
                'ThuTu'           => 11,
                'TenMoc'          => '11. Nộp báo cáo khóa luận chính thức & hoàn tất hồ sơ',
                'DoiTuongThucHien'=> 'Sinh viên & Giáo vụ',
                'NgayBatDau'      => $start->copy()->addDays(86)->format('Y-m-d'),
                'NgayKetThuc'     => $start->copy()->addDays(90)->format('Y-m-d'),
                'MoTa'            => 'Nộp bản thuyết minh và sản phẩm đóng gói chính thức lên hệ thống.',
                'BatBuoc'         => true,
            ],
            [
                'LoaiGiaiDoan'    => 'BAO_VE',
                'ThuTu'           => 12,
                'TenMoc'          => '12. Lễ bảo vệ khóa luận trước Hội đồng chấm',
                'DoiTuongThucHien'=> 'Hội đồng & Sinh viên',
                'NgayBatDau'      => $start->copy()->addDays(95)->format('Y-m-d'),
                'NgayKetThuc'     => $start->copy()->addDays(100)->format('Y-m-d'),
                'MoTa'            => 'Tổ chức các buổi bảo vệ khóa luận trước Hội đồng và tổng kết công bố điểm số.',
                'BatBuoc'         => true,
            ],
        ];
    }
}
