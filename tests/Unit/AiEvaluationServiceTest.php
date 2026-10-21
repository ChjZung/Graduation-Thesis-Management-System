<?php
namespace Tests\Unit;
require_once __DIR__.'/../../vendor/autoload.php';
use App\Services\AiEvaluationService;
use PHPUnit\Framework\TestCase;

class AiEvaluationServiceTest extends TestCase
{
    public function test_without_annotations_quality_is_not_estimated(): void
    {
        $result=(new AiEvaluationService)->evaluate([],null);
        $this->assertNull($result['precision']); $this->assertNull($result['recall']); $this->assertNull($result['f1']);
    }
    public function test_wrong_group_is_both_false_positive_and_false_negative(): void
    {
        $truth=['completed_work'=>['Đã thiết kế CSDL.'],'difficulties'=>[],'next_steps'=>[]];
        $prediction=['completed_work'=>[],'difficulties'=>[],'next_steps'=>[['evidence'=>'Đã thiết kế CSDL.']]];
        $result=(new AiEvaluationService)->evaluate($prediction,$truth);
        $this->assertSame(0,$result['tp']); $this->assertSame(1,$result['fp']); $this->assertSame(1,$result['fn']); $this->assertSame(0,$result['f1']);
    }
    public function test_duplicate_quotes_do_not_inflate_quality(): void
    {
        $truth=['completed_work'=>['Đã thiết kế CSDL.'],'difficulties'=>[],'next_steps'=>[]];
        $result=(new AiEvaluationService)->evaluate(['completed_work'=>[['evidence'=>'Đã thiết kế CSDL.'],['evidence'=>'Đã thiết kế CSDL.']]],$truth);
        $this->assertSame(1,$result['tp']); $this->assertSame(1,$result['f1']);
    }
    public function test_empty_truth_and_prediction_do_not_create_a_perfect_score(): void
    {
        $result=(new AiEvaluationService)->evaluate([],['completed_work'=>[],'difficulties'=>[],'next_steps'=>[]]);
        $this->assertNull($result['f1']);
    }
}
