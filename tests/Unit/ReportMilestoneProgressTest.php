<?php

namespace Tests\Unit;

use App\Services\ReportMilestoneProgressService;
use PHPUnit\Framework\TestCase;

class ReportMilestoneProgressTest extends TestCase
{
    private function round(bool $done = true, string $status = 'Đạt'): array
    {
        return ['confirmed'=>$done, 'status'=>$status];
    }

    public function test_unequal_round_counts_have_equal_stage_weights(): void
    {
        $result = (new ReportMilestoneProgressService)->calculate([
            ['rounds'=>[$this->round(), $this->round(false)]],
            ['rounds'=>[$this->round(), $this->round(), $this->round(false), $this->round(false)]],
        ]);
        $this->assertEqualsWithDelta(50, $result['percentage'], .000001);
        $this->assertEqualsWithDelta(25, $result['stages'][0]['rounds'][0]['weight'], .000001);
        $this->assertEqualsWithDelta(12.5, $result['stages'][1]['rounds'][0]['weight'], .000001);
    }

    public function test_uploaded_rejected_or_unconfirmed_are_not_completed(): void
    {
        $result = (new ReportMilestoneProgressService)->calculate([['rounds'=>[
            $this->round(false, 'Chờ duyệt'), $this->round(true, 'Yêu cầu nộp lại'), $this->round(false),
        ]]]);
        $this->assertSame(0.0, $result['percentage']);
        $this->assertSame(0, $result['completed_rounds']);
    }

    public function test_empty_stages_and_dynamic_configuration_do_not_reallocate_missing_weight(): void
    {
        $service = new ReportMilestoneProgressService;
        $this->assertSame(0.0, $service->calculate([])['percentage']);
        $result = $service->calculate([['rounds'=>[$this->round()]], ['rounds'=>[]]]);
        $this->assertSame(50.0, $result['percentage']);
        $this->assertTrue($result['configuration_incomplete']);
    }

    public function test_no_early_rounding(): void
    {
        $result = (new ReportMilestoneProgressService)->calculate(array_fill(0, 3,
            ['rounds'=>array_fill(0, 3, $this->round())]));
        $this->assertEqualsWithDelta(100.0, $result['percentage'], .0000001);
        $this->assertSame(9, $result['completed_rounds']);
    }
}
