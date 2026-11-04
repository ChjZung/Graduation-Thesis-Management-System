<?php

namespace Tests\Feature;

use App\Http\Controllers\CalendarController;
use App\Models\MocThoiGianKhoaLuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CalendarMilestonePlacementTest extends TestCase
{
    private function schedule(string $date, array $milestones, string $role = 'SINH_VIEN'): array
    {
        Auth::shouldReceive('check')->andReturn(false);
        $method = new \ReflectionMethod(CalendarController::class, 'buildWeeklyScheduleData');
        $method->setAccessible(true);

        return $method->invoke(new CalendarController(), Request::create('/calendar', 'GET', ['date' => $date]), collect($milestones), $role);
    }

    private function milestone(string $id, string $start, string $end): MocThoiGianKhoaLuan
    {
        return new MocThoiGianKhoaLuan([
            'MaMoc' => $id,
            'MakeHoach' => 'PLAN_A',
            'TenMoc' => 'Thời gian thực hiện khóa luận',
            'DoiTuongThucHien' => 'Sinh viên & GVHD',
            'NgayBatDau' => $start,
            'NgayKetThuc' => $end,
        ]);
    }

    public function test_shared_long_milestone_appears_once_and_still_spans_each_week(): void
    {
        $milestone = $this->milestone('M6', '2026-08-17', '2026-11-08');
        foreach (['2026-11-05', '2026-10-29'] as $date) {
            $events = $this->schedule($date, [$milestone])['shiftEvents']['SINH_VIEN'];
            $this->assertCount(1, $events);
            $this->assertSame(7, $events[0]['span']);
            $this->assertSame('M6', $events[0]['ma_moc']);
        }
    }

    public function test_contact_milestone_keeps_week_boundary_and_location(): void
    {
        $milestone = $this->milestone('M5', '2026-08-14', '2026-08-17');
        $milestone->TenMoc = 'Sinh viên chủ động liên hệ GVHD qua email';
        $events = $this->schedule('2026-08-10', [$milestone])['shiftEvents']['SINH_VIEN'];
        $this->assertCount(1, $events);
        $this->assertSame(4, $events[0]['col_start']);
        $this->assertSame(3, $events[0]['span']);
        $this->assertSame('Email GVHD', $events[0]['room']);
        $next = $this->schedule('2026-08-17', [$milestone])['shiftEvents']['SINH_VIEN'];
        $this->assertCount(1, $next);
        $this->assertSame(1, $next[0]['span']);
    }

    public function test_different_milestones_with_same_title_are_not_removed(): void
    {
        $first = $this->milestone('M_A', '2026-11-02', '2026-11-08');
        $second = $this->milestone('M_B', '2026-11-02', '2026-11-08');
        $second->MakeHoach = 'PLAN_B';
        $events = $this->schedule('2026-11-05', [$first, $second])['shiftEvents']['SINH_VIEN'];
        $this->assertSame(['M_A', 'M_B'], array_column($events, 'ma_moc'));
    }

    public function test_staff_view_retains_both_audience_rows(): void
    {
        $events = $this->schedule('2026-11-05', [$this->milestone('M6', '2026-08-17', '2026-11-08')], 'GIANG_VIEN')['shiftEvents'];
        $this->assertCount(1, $events['SINH_VIEN']);
        $this->assertCount(1, $events['GIANG_VIEN']);
    }
}
