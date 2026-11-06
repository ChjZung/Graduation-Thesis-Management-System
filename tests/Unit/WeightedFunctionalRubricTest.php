<?php

namespace Tests\Unit;

use App\Models\PhieuChamDiem;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class WeightedFunctionalRubricTest extends TestCase
{
    private function rubric(string $cap): string
    {
        return "1. Nhóm A [nhóm: 0,75 | CLO3]\n  • Một [điểm: 0,25]\n  • Hai\n"
            . "2. Nhóm B [nhóm: $cap | CLO2.1]\n"
            . implode("\n", array_map(fn ($n) => "  • Yêu cầu $n", range(1, 7)));
    }

    public function test_group_caps_and_clo_inheritance_for_both_school_forms(): void
    {
        foreach (['KLCN'=>['2,75',350], 'KLKS'=>['3,25',400]] as $type => [$cap, $total]) {
            $items = array_values(array_filter(
                PhieuChamDiem::parseFunctionalRequirements($this->rubric($cap), $type),
                fn ($item) => $item['id'] !== 'tc7_co_ban'
            ));
            $this->assertSame(9, count($items));
            $this->assertSame([0.25, 0.5], array_column(array_slice($items, 0, 2), 'max'));
            $this->assertSame('CLO2.1', $items[2]['clo']);
            $this->assertSame($total, array_sum(array_map(fn ($item) => (int) round($item['max'] * 100), $items)));
        }
    }

    public function test_rejects_total_above_school_form(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PhieuChamDiem::parseFunctionalRequirements($this->rubric('3,25'), 'KLCN');
    }

    public function test_rejects_child_scores_above_parent(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PhieuChamDiem::parseFunctionalRequirements(str_replace('  • Hai', '  • Hai [điểm: 0,50]', str_replace('0,25', '0,30', $this->rubric('2,75'))));
    }
}
