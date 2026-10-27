<?php
namespace Tests\Unit;
use App\Support\AiReportPresentation;
use PHPUnit\Framework\TestCase;
class AiTeacherPresentationTest extends TestCase
{
    public function test_reused_quotes_count_once_and_missing_measurement_is_not_zero_accuracy(): void
    {
        $ref=['page'=>2,'quote'=>'Câu trích nguồn','quote_verified'=>true];
        $m=AiReportPresentation::metrics(['document_content'=>[['source_references'=>[$ref,$ref]]],'completed_work'=>[['source_references'=>[$ref]]]]);
        $this->assertSame(1,$m['matched']);$this->assertSame(1,$m['references']);
        unset($ref['quote_verified']);$m=AiReportPresentation::metrics(['document_content'=>[['source_references'=>[$ref]]]]);
        $this->assertSame(0,$m['measured']);$this->assertNull($m['total']);
    }
    public function test_coverage_is_separate_from_matching_and_unlinked_parts_are_counted(): void
    {
        $m=AiReportPresentation::metrics(['analysis'=>['analyzed_chunks'=>3,'total_chunks'=>7],
          'chapters'=>[['items'=>[]],['items'=>[['content'=>'x']]]],
          'teacher_requirements'=>[['report_relation'=>'not_found'],['report_relation'=>'scope_note']]]);
        $this->assertSame(3,$m['done']);$this->assertSame(7,$m['total']);
        $this->assertSame(1,$m['empty_chapters']);$this->assertSame(1,$m['unlinked_requirements']);
        $this->assertSame(0,$m['measured']);
    }
}
