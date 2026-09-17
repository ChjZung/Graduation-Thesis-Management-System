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
            $events[] = [
                'id' => $moc->MaMoc,
                'title' => $moc->TenMoc,
                'start' => $moc->NgayBatDau,
                'end' => date('Y-m-d', strtotime($moc->NgayKetThuc . ' +1 day')),
                'color' => $colors[$i % count($colors)],
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

    public function adminCalendar()
    {
        $events = $this->getCalendarEvents();
        $nextMilestone = $this->getNextUpcomingMilestone();
        return view('calendar.index', [
            'layout' => 'layouts.admin',
            'events' => $events,
            'nextMilestone' => $nextMilestone,
            'roleTitle' => 'Giáo Vụ Khoa',
        ]);
    }

    public function giangVienCalendar()
    {
        $events = $this->getCalendarEvents();
        $nextMilestone = $this->getNextUpcomingMilestone();
        return view('calendar.index', [
            'layout' => 'layouts.giangvien',
            'events' => $events,
            'nextMilestone' => $nextMilestone,
            'roleTitle' => 'Giảng Viên',
        ]);
    }

    public function sinhVienCalendar()
    {
        $events = $this->getCalendarEvents();
        $nextMilestone = $this->getNextUpcomingMilestone();
        return view('calendar.index', [
            'layout' => 'layouts.sinhvien',
            'events' => $events,
            'nextMilestone' => $nextMilestone,
            'roleTitle' => 'Sinh Viên',
        ]);
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
            $NhomQuery->whereHas('dangKyDeTai', function($q) use ($request) {
                $q->where('MaGVHuongDan', $request->MaGV);
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

        return view('calendar.schedule_matrix', compact('layout', 'keHoachs', 'activePlan', 'matrix', 'Nhom', 'hocKies', 'giangViens'));
    }
}
