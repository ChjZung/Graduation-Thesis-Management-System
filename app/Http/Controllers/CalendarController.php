<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MocThoiGianKhoaLuan;
use App\Models\KeHoachKhoaLuan;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    private function getCalendarEvents()
    {
        $mocs = MocThoiGianKhoaLuan::with('keHoach')->get();
        $events = [];

        $colors = [
            '#0072CE', // Mốc 1: Phân tích Nghiệp vụ (HUIT Blue)
            '#0D6EFD', // Mốc 2: Phân tích Hệ thống
            '#FFC107', // Mốc 3: Thiết kế CSDL (Warning Yellow)
            '#FD7E14', // Mốc 4: Triển khai Code (Orange)
            '#198754', // Mốc 5: Hoàn thành & Bổ sung (Success Green)
            '#DC3545', // Hạn Turnitin / Bảo vệ (Danger Red)
        ];

        foreach ($mocs as $i => $moc) {
            $color = match($moc->LoaiGiaiDoan) {
                'TAO_NHOM', 'DANG_KY_DE_TAI', 'XU_LY_NGOAI_LE' => '#0072CE',
                'CONG_BO_GVHD', 'LIEN_HE_GVHD' => '#0D6EFD',
                'THUC_HIEN' => '#6F42C1',
                'BAO_CAO_TIEN_DO_1', 'BAO_CAO_TIEN_DO_2', 'BAO_CAO_TIEN_DO_3' => '#FD7E14',
                'DAO_VAN' => '#DC3545',
                'GVHD_XAC_NHAN' => '#20C997',
                'NOP_BAO_CAO' => '#0D6EFD',
                'BAO_VE' => '#198754',
                default => $colors[$i % count($colors)],
            };

            $events[] = [
                'id' => $moc->MaMoc,
                'title' => $moc->TenMoc,
                'start' => $moc->NgayBatDau,
                'end' => date('Y-m-d', strtotime($moc->NgayKetThuc . ' +1 day')),
                'color' => $color,
                'description' => $moc->MoTa ?? 'Hạn nộp báo cáo theo quy định',
                'keHoach' => $moc->keHoach->TenKeHoach ?? '',
            ];
        }

        return $events;
    }

    private function getNextUpcomingMilestone()
    {
        $today = now()->format('Y-m-d');
        return MocThoiGianKhoaLuan::where('NgayKetThuc', '>=', $today)
            ->orderBy('NgayKetThuc', 'asc')
            ->first();
    }

    public function adminCalendar(Request $request)
    {
        $events = $this->getCalendarEvents();
        $nextMilestone = $this->getNextUpcomingMilestone();
        $allMilestones = MocThoiGianKhoaLuan::with('keHoach')->orderBy('NgayBatDau', 'asc')->get();
        $plans = KeHoachKhoaLuan::orderBy('created_at', 'desc')->get();
        $activePlan = KeHoachKhoaLuan::whereIn('TrangThai', ['ĐANG THỰC HIỆN', 'ĐÃ CÔNG BỐ'])->first() ?? $plans->first();
        $weeklyData = $this->buildWeeklyScheduleData($request, $allMilestones);

        return view('calendar.index', array_merge([
            'layout'        => 'layouts.admin',
            'events'        => $events,
            'nextMilestone' => $nextMilestone,
            'allMilestones' => $allMilestones,
            'plans'         => $plans,
            'activePlan'    => $activePlan,
            'roleTitle'     => 'Giáo Vụ Khoa',
        ], $weeklyData));
    }

    public function giangVienCalendar(Request $request)
    {
        $events = $this->getCalendarEvents();
        $nextMilestone = $this->getNextUpcomingMilestone();
        $allMilestones = MocThoiGianKhoaLuan::with('keHoach')->orderBy('NgayBatDau', 'asc')->get();
        $plans = KeHoachKhoaLuan::orderBy('created_at', 'desc')->get();
        $activePlan = KeHoachKhoaLuan::whereIn('TrangThai', ['ĐANG THỰC HIỆN', 'ĐÃ CÔNG BỐ'])->first() ?? $plans->first();
        $weeklyData = $this->buildWeeklyScheduleData($request, $allMilestones);

        return view('giangvien.calendar', array_merge([
            'events'        => $events,
            'nextMilestone' => $nextMilestone,
            'allMilestones' => $allMilestones,
            'plans'         => $plans,
            'activePlan'    => $activePlan,
            'roleTitle'     => 'Giảng Viên',
        ], $weeklyData));
    }

    public function sinhVienCalendar(Request $request)
    {
        $events = $this->getCalendarEvents();
        $nextMilestone = $this->getNextUpcomingMilestone();
        $allMilestones = MocThoiGianKhoaLuan::with('keHoach')->orderBy('NgayBatDau', 'asc')->get();
        $plans = KeHoachKhoaLuan::orderBy('created_at', 'desc')->get();
        $activePlan = KeHoachKhoaLuan::whereIn('TrangThai', ['ĐANG THỰC HIỆN', 'ĐÃ CÔNG BỐ'])->first() ?? $plans->first();
        $weeklyData = $this->buildWeeklyScheduleData($request, $allMilestones);

        return view('calendar.index', array_merge([
            'layout'        => 'layouts.sinhvien',
            'events'        => $events,
            'nextMilestone' => $nextMilestone,
            'allMilestones' => $allMilestones,
            'plans'         => $plans,
            'activePlan'    => $activePlan,
            'roleTitle'     => 'Sinh Viên',
        ], $weeklyData));
    }

    private function buildWeeklyScheduleData(Request $request, $allMilestones): array
    {
        // 1. Xác định ngày tham chiếu (Target Date)
        $dateParam = $request->input('date');
        try {
            $currentDate = $dateParam ? \Carbon\Carbon::parse($dateParam) : \Carbon\Carbon::parse('2026-10-09');
        } catch (\Throwable $e) {
            $currentDate = \Carbon\Carbon::parse('2026-10-09');
        }

        // Lấy Thứ 2 (bắt đầu tuần) và Chủ Nhật (kết thúc tuần)
        $startOfWeek = $currentDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        $endOfWeek = $currentDate->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);

        $weekDays = [];
        $dayNames = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật'];
        for ($i = 0; $i < 7; $i++) {
            $dayCarbon = $startOfWeek->copy()->addDays($i);
            $isoDate = $dayCarbon->format('Y-m-d');
            $weekDays[] = [
                'day_name'   => $dayNames[$i],
                'date_str'   => $dayCarbon->format('d/m/Y'),
                'iso_date'   => $isoDate,
                'is_today'   => ($isoDate === now()->format('Y-m-d')),
                'is_current' => ($isoDate === '2026-10-09'),
            ];
        }

        // 2. Định nghĩa các hàng phân loại theo Đối Tượng & Nhóm Quy Trình Khóa Luận (Thay cho ca sáng/chiều/tối)
        $shifts = [
            'SINH_VIEN' => [
                'name'  => 'Sinh Viên',
                'icon'  => 'fa-solid fa-user-graduate',
                'desc'  => 'Tác vụ, đăng ký & nộp báo cáo',
                'bg'    => '#f0f9ff',
                'color' => '#0369a1',
            ],
            'GIANG_VIEN' => [
                'name'  => 'GV Hướng Dẫn',
                'icon'  => 'fa-solid fa-chalkboard-user',
                'desc'  => 'Hướng dẫn & duyệt tiến độ',
                'bg'    => '#f0fdf4',
                'color' => '#15803d',
            ],
            'KHOA_HDS' => [
                'name'  => 'Khoa & Hội Đồng',
                'icon'  => 'fa-solid fa-building-columns',
                'desc'  => 'VPK & Chấm bảo vệ',
                'bg'    => '#fffbeb',
                'color' => '#854d0e',
            ],
        ];

        // 3. Bảng màu nhận diện riêng biệt cho từng mốc (Tránh trùng lặp màu, nhận diện tức thì)
        $milestonePalette = [
            1 => ['bg' => '#f5f3ff', 'border' => '#8b5cf6', 'text' => '#5b21b6', 'badge' => '#6d28d9', 'badge_bg' => '#ede9fe'], // Tím (HUIT-STUDENT)
            2 => ['bg' => '#f0f9ff', 'border' => '#0284c7', 'text' => '#0369a1', 'badge' => '#0284c7', 'badge_bg' => '#e0f2fe'], // Xanh dương (Đăng ký đề tài)
            3 => ['bg' => '#fff7ed', 'border' => '#ea580c', 'text' => '#9a3412', 'badge' => '#ea580c', 'badge_bg' => '#ffedd5'], // Cam đậm (Ngoại lệ VPK)
            4 => ['bg' => '#f0fdfa', 'border' => '#0d9488', 'text' => '#115e59', 'badge' => '#0d9488', 'badge_bg' => '#ccfbf1'], // Xanh ngọc (Công bố GVHD)
            5 => ['bg' => '#f0fdf4', 'border' => '#16a34a', 'text' => '#166534', 'badge' => '#16a34a', 'badge_bg' => '#dcfce7'], // Xanh lục (Liên hệ GVHD)
            6 => ['bg' => '#f8fafc', 'border' => '#64748b', 'text' => '#334155', 'badge' => '#64748b', 'badge_bg' => '#e2e8f0'], // Xám ghi (12 tuần)
            7 => ['bg' => '#fff1f2', 'border' => '#e11d48', 'text' => '#9f1239', 'badge' => '#e11d48', 'badge_bg' => '#ffe4e6'], // Đỏ hồng (Nộp báo cáo)
            8 => ['bg' => '#fffbeb', 'border' => '#d97706', 'text' => '#92400e', 'badge' => '#d97706', 'badge_bg' => '#fef3c7'], // Vàng hổ phách (Lịch Hội đồng)
            9 => ['bg' => '#faf5ff', 'border' => '#9333ea', 'text' => '#6b21a8', 'badge' => '#9333ea', 'badge_bg' => '#f3e8ff'], // Tím sẫm (Bảo vệ Hội đồng)
        ];

        // 4. Tính toán sự kiện trong tuần theo độ dài thời hạn (Spanning Event Bar, KHÔNG lặp mỗi ngày 1 cục)
        $shiftEvents = [
            'SINH_VIEN'  => [],
            'GIANG_VIEN' => [],
            'KHOA_HDS'   => [],
        ];
        $macroPhase = null;

        foreach ($allMilestones as $idx => $m) {
            $start = $m->NgayBatDau ? \Carbon\Carbon::parse($m->NgayBatDau) : null;
            $end = $m->NgayKetThuc ? \Carbon\Carbon::parse($m->NgayKetThuc) : null;
            if (!$start || !$end) continue;

            $doiTuong = mb_strtolower($m->DoiTuongThucHien ?? '');
            $titleLower = mb_strtolower($m->TenMoc ?? '');
            $mota = $m->MoTa ?? '';
            $mocIndex = $idx + 1;
            $palette = $milestonePalette[$mocIndex] ?? $milestonePalette[1];

            // Nếu là giai đoạn vĩ mô kéo dài nhiều tuần (ví dụ giai đoạn 12 tuần làm khóa luận), lưu macroPhase banner
            $daysTotal = $start->diffInDays($end) + 1;
            if ($daysTotal > 14) {
                if ($start->lte($endOfWeek) && $end->gte($startOfWeek)) {
                    $macroPhase = [
                        'title'      => $m->TenMoc,
                        'moc_index'  => $mocIndex,
                        'start_str'  => $start->format('d/m/Y'),
                        'end_str'    => $end->format('d/m/Y'),
                        'weeks'      => round($daysTotal / 7),
                        'palette'    => $palette,
                    ];
                }
            }

            // Kiểm tra mốc có giao cắt với tuần đang xem hay không
            if ($start->lte($endOfWeek) && $end->gte($startOfWeek)) {
                $effStart = $start->copy()->max($startOfWeek);
                $effEnd = $end->copy()->min($endOfWeek);

                $colStart = (int) $startOfWeek->diffInDays($effStart); // 0 -> 6
                $colEnd = (int) $startOfWeek->diffInDays($effEnd);     // 0 -> 6
                $span = $colEnd - $colStart + 1;

                // Định dạng nhãn khoảng thời gian
                if ($daysTotal > 14) {
                    $dateRange = $start->format('d/m') . ' → ' . $end->format('d/m') . ' (' . round($daysTotal / 7) . ' tuần)';
                } else {
                    $dateRange = $start->format('d/m') . ($start->ne($end) ? ' → ' . $end->format('d/m') : '');
                }

                // Lọc bỏ thông tin nhiễu: Chỉ giữ khung giờ cụ thể và địa điểm cụ thể
                $timeSlot = null;
                if (str_contains($mota, '8h00 - 20h00') || str_contains($titleLower, '8h00 - 20h00') || str_contains($mota, 'DANG_KY_DE_TAI')) {
                    $timeSlot = '08:00 - 20:00';
                } elseif (str_contains($mota, '8h00 - 11h00') || str_contains($titleLower, '8h00 - 11h00') || str_contains($mota, 'XU_LY_NGOAI_LE')) {
                    $timeSlot = '08:00 - 11:00 & 13:30 - 16:00';
                } elseif (str_contains($mota, 'NOP_BAO_CAO') || str_contains($titleLower, 'nộp báo cáo')) {
                    $timeSlot = 'Hạn chót: 23:59';
                }

                $room = null;
                if (str_contains($mota, 'HUIT-STUDENT') || str_contains($mota, 'TAO_NHOM') || str_contains($mota, 'DANG_KY_DE_TAI') || str_contains($titleLower, 'huit-student')) {
                    $room = 'Phần mềm HUIT-STUDENT';
                } elseif (str_contains($mota, 'XU_LY_NGOAI_LE') || str_contains($titleLower, 'vpk')) {
                    $room = 'VPK gặp Cô Duyên (Giáo vụ)';
                } elseif (str_contains($mota, 'CONG_BO_GVHD') || str_contains($mota, 'THONG_BAO_HOI_DONG') || str_contains($titleLower, 'website') || str_contains($mota, 'fit.huit.edu.vn')) {
                    $room = 'Website: fit.huit.edu.vn';
                } elseif (str_contains($mota, 'LIEN_HE_GVHD') || str_contains($titleLower, 'email')) {
                    $room = 'Email GVHD';
                } elseif (str_contains($mota, 'BAO_VE') || str_contains($titleLower, 'hội đồng') || str_contains($titleLower, 'bảo vệ')) {
                    $room = 'Hội đồng chấm bảo vệ';
                }

                $item = [
                    'moc_index'  => $mocIndex,
                    'ma_moc'     => $m->MaMoc,
                    'title'      => $m->TenMoc,
                    'col_start'  => $colStart,
                    'col_end'    => $colEnd,
                    'span'       => $span,
                    'time_slot'  => $timeSlot,
                    'room'       => $room,
                    'palette'    => $palette,
                    'is_start'   => $effStart->eq($start),
                    'is_end'     => $effEnd->eq($end),
                    'date_range' => $dateRange,
                    'days_count' => $daysTotal,
                ];

                // Phân bổ vào đúng hàng đối tượng thực hiện
                $matched = false;
                if (str_contains($doiTuong, 'sinh viên') || str_contains($doiTuong, 'nhóm trưởng')) {
                    $shiftEvents['SINH_VIEN'][] = $item;
                    $matched = true;
                }
                if (str_contains($doiTuong, 'gvhd') || str_contains($doiTuong, 'giảng viên')) {
                    $shiftEvents['GIANG_VIEN'][] = $item;
                    $matched = true;
                }
                if (str_contains($doiTuong, 'khoa') || str_contains($doiTuong, 'giáo vụ') || str_contains($doiTuong, 'hội đồng')) {
                    $shiftEvents['KHOA_HDS'][] = $item;
                    $matched = true;
                }
                if (!$matched) {
                    if (str_contains($titleLower, 'hội đồng') || str_contains($titleLower, 'vpk') || str_contains($titleLower, 'khoa')) {
                        $shiftEvents['KHOA_HDS'][] = $item;
                    } else {
                        $shiftEvents['SINH_VIEN'][] = $item;
                    }
                }
            }
        }

        return [
            'weekDays'         => $weekDays,
            'shifts'           => $shifts,
            'shiftEvents'      => $shiftEvents,
            'macroPhase'       => $macroPhase,
            'milestonePalette' => $milestonePalette,
            'currentDate'      => $currentDate->format('Y-m-d'),
            'currentDateLabel' => $currentDate->format('d/m/Y'),
            'prevWeekDate'     => $startOfWeek->copy()->subWeek()->format('Y-m-d'),
            'nextWeekDate'     => $startOfWeek->copy()->addWeek()->format('Y-m-d'),
            'todayDate'        => '2026-10-09',
        ];
    }

    /**
     * Lịch Quy Trình Khóa Luận (So Sánh Kế Hoạch vs Thực Tế Chi Tiết)
     */
    public function scheduleMatrix(Request $request)
    {
        $user = Auth::user();
        $layout = 'layouts.admin';
        if ($user && $user->VaiTro === 'Giảng viên') {
            $layout = 'layouts.giangvien';
        } elseif ($user && $user->VaiTro === 'Sinh viên') {
            $layout = 'layouts.sinhvien';
        }

        $keHoachs = KeHoachKhoaLuan::with(['mocThoiGians', 'hocKy'])->orderBy('created_at', 'desc')->get();
        
        $selectedPlanId = $request->input('MaKeHoach');
        $activePlan = $selectedPlanId 
            ? KeHoachKhoaLuan::with(['mocThoiGians', 'hocKy'])->find($selectedPlanId) 
            : \App\Services\PlanPhaseService::getActivePlan();

        if (!$activePlan && $keHoachs->isNotEmpty()) {
            $activePlan = $keHoachs->first();
        }

        $NhomQuery = \App\Models\Nhom::with(['truongNhom', 'thanhViens.sinhVien', 'deTai', 'dangKyDeTai.giangVienHuongDan', 'baoCaos', 'hoSoBaoVe']);

        if ($request->filled('MaGV')) {
            $NhomQuery->where(function($q) use ($request) {
                $q->whereHas('dangKyDeTai.deTai', function($dtQ) use ($request) {
                    $dtQ->where('MaGV', $request->MaGV);
                })->orWhereHas('deTai', function($dtQ) use ($request) {
                    $dtQ->where('MaGV', $request->MaGV);
                });
            });
        }

        $Nhom = $NhomQuery->get();
        $today = \Carbon\Carbon::today();

        // Biến đổi Ma trận Kế Hoạch vs Thực Tế
        $matrix = [];
        if ($activePlan) {
            foreach ($activePlan->mocThoiGians as $moc) {
                $mocInfo = [
                    'moc' => $moc,
                    'status' => \App\Services\PlanPhaseService::getPhaseStatus($moc),
                    'teams' => [],
                ];

                foreach ($Nhom as $nhom) {
                    $taskStatus = 'CHUA_BAT_DAU';
                    $actualDate = null;
                    $warning = null;

                    $startDate = \Carbon\Carbon::parse($moc->NgayBatDau);
                    $endDate = \Carbon\Carbon::parse($moc->NgayKetThuc);
                    $daysLeft = (int) $today->diffInDays($endDate, false);

                    if (str_contains($moc->LoaiGiaiDoan, 'BAO_CAO_TIEN_DO')) {
                        $lan = str_contains($moc->LoaiGiaiDoan, '1') ? 1 : (str_contains($moc->LoaiGiaiDoan, '2') ? 2 : 3);
                        $bc = $nhom->baoCaos->where('LanBaoCao', $lan)->first();
                        if ($bc) {
                            $taskStatus = 'HOAN_THANH';
                            $actualDate = date('d/m/Y', strtotime($bc->NgayNop));
                        } else {
                            if ($daysLeft < 0) {
                                $taskStatus = 'QUA_HAN';
                                $warning = "Quá hạn " . abs($daysLeft) . " ngày";
                            } elseif ($daysLeft <= 7 && $daysLeft >= 0) {
                                $taskStatus = 'SAP_DEN_HAN';
                                $warning = "Còn " . $daysLeft . " ngày";
                            } else {
                                $taskStatus = $today->between($startDate, $endDate) ? 'DANG_DIEN_RA' : 'CHUA_BAT_DAU';
                            }
                        }
                    } elseif ($moc->LoaiGiaiDoan === 'DAO_VAN') {
                        if ($nhom->hoSoBaoVe && $nhom->hoSoBaoVe->TyLeTrungLap !== null) {
                            $taskStatus = 'HOAN_THANH';
                            $actualDate = "Turnitin: " . $nhom->hoSoBaoVe->TyLeTrungLap . "%";
                        } else {
                            $taskStatus = ($daysLeft < 0) ? 'QUA_HAN' : (($daysLeft <= 7) ? 'SAP_DEN_HAN' : 'CHUA_BAT_DAU');
                        }
                    } elseif ($moc->LoaiGiaiDoan === 'GVHD_XAC_NHAN') {
                        if ($nhom->hoSoBaoVe && $nhom->hoSoBaoVe->XacNhanGVHD) {
                            $taskStatus = 'HOAN_THANH';
                            $actualDate = "Đã xác nhận";
                        } else {
                            $taskStatus = ($daysLeft < 0) ? 'QUA_HAN' : (($daysLeft <= 7) ? 'SAP_DEN_HAN' : 'CHUA_BAT_DAU');
                        }
                    } else {
                        // Mốc chung (Đăng ký, Phân công, Bảo vệ...)
                        if ($daysLeft < 0) {
                            $taskStatus = 'QUA_HAN';
                        } elseif ($today->between($startDate, $endDate)) {
                            $taskStatus = 'DANG_DIEN_RA';
                        } else {
                            $taskStatus = 'CHUA_BAT_DAU';
                        }
                    }

                    $mocInfo['teams'][$nhom->MaNhom] = [
                        'nhom'        => $nhom,
                        'status'      => $taskStatus,
                        'actual_date' => $actualDate,
                        'warning'     => $warning,
                    ];
                }

                $matrix[] = $mocInfo;
            }
        }

        $hocKies = \App\Models\HocKy::all();
        $giangViens = \App\Models\GiangVien::all();
        $nhoms = $Nhom;

        return view('calendar.schedule_matrix', compact('layout', 'keHoachs', 'activePlan', 'matrix', 'Nhom', 'nhoms', 'hocKies', 'giangViens'));
    }
}
