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
}
