<?php
namespace Tests\Unit;
use App\Support\AiRequirementCoverage as Coverage;
use PHPUnit\Framework\TestCase;
final class AiRequirementCoverageTest extends TestCase
{
    private function point(string $title,bool $found=true,array $extra=[]): array
    {
        return array_replace(['title'=>$title,'relation'=>$found?'related_text':'not_found',
            'source_references'=>$found?[['page'=>3,'quote'=>'Nguồn đã kiểm chứng','quote_verified'=>true]]:[]],$extra);
    }
    private function data(array $items): array {return ['teacher_requirements'=>$items,'requirements_review'=>['status'=>'completed']];}
    public function test_five_flat_requirements_are_twenty_percent_each(): void
    {
        $items=[];for($i=1;$i<=5;$i++)$items[]=['text'=>"$i. Mục $i",'report_points'=>[$this->point("Mục $i",$i<=3)]];
        $result=Coverage::calculate($this->data($items));
        $this->assertSame(60.0,$result['percent']);$this->assertSame(3,$result['found']);$this->assertFalse($result['provisional']);
        foreach($result['rows'] as $row)$this->assertEquals(20,$row['weight']);
    }
    public function test_four_requirements_and_recursive_children_never_double_count_parent(): void
    {
        $items=[['text'=>"1. Cơ sở dữ liệu\n1.1. ERD\n1.2. Bảng dữ liệu\n1.2.1. Khóa chính\n1.2.2. Khóa ngoại",
            'report_points'=>[$this->point('ERD'),$this->point('Khóa chính'),$this->point('Cơ sở dữ liệu')]]];
        for($i=2;$i<=4;$i++)$items[]=['text'=>"$i. Mục $i",'report_points'=>[]];
        $result=Coverage::calculate($this->data($items));
        $this->assertSame(18.75,$result['percent']); // 12.5 + 6.25; parent references cannot fill child 1.2.2.
        $this->assertSame(6,$result['leaves']);$this->assertSame(2,$result['found']);
    }
    public function test_five_requirements_first_with_four_children_contributes_fifteen_percent(): void
    {
        $items=[['text'=>"1. Thiết kế\n  - ERD\n  - Bảng dữ liệu\n  - Khóa chính\n  - Khóa ngoại",
            'report_points'=>[$this->point('ERD'),$this->point('Bảng dữ liệu'),$this->point('Khóa chính')]]];
        for($i=2;$i<=5;$i++)$items[]=['text'=>"$i. Mục $i",'report_points'=>[]];
        $this->assertSame(15.0,Coverage::calculate($this->data($items))['percent']);
    }
    public function test_generated_checks_and_duplicate_sources_do_not_change_denominator(): void
    {
        $result=Coverage::calculate($this->data([['text'=>'1. Thiết kế cơ sở dữ liệu','report_points'=>array_fill(0,8,$this->point('Một ý AI sinh ra'))]]));
        $this->assertSame(1,$result['leaves']);$this->assertSame(100.0,$result['percent']);
    }
    public function test_caption_is_counted_only_as_a_verified_pointer(): void
    {
        $p=$this->point('ERD');$p['source_references'][0]['source_type']='CAPTION_REFERENCE';
        $result=Coverage::calculate($this->data([['text'=>'ERD','report_points'=>[$p]]]));
        $this->assertSame(1,$result['caption_only']);$this->assertSame(100.0,$result['percent']);
        $p['source_references'][0]['quote_verified']=false;
        $this->assertSame(0.0,Coverage::calculate($this->data([['text'=>'ERD','report_points'=>[$p]]]))['percent']);
    }
    public function test_unverified_or_rejected_sources_do_not_contribute(): void
    {
        $p=$this->point('ERD');$p['source_references'][0]['semantic_support']='DOES_NOT_SUPPORT';
        $this->assertSame(0.0,Coverage::calculate($this->data([['text'=>'ERD','report_points'=>[$p]]]))['percent']);
    }
    public function test_stale_empty_and_legacy_contexts_have_no_percentage(): void
    {
        foreach(['stale','legacy_unverified'] as $binding){$data=$this->data([['text'=>'ERD']]);$data['requirements_binding_status']=$binding;
            $this->assertNull(Coverage::calculate($data)['percent']);}
        $this->assertNull(Coverage::calculate([])['percent']);
    }
    public function test_partial_or_running_result_is_provisional_and_calculation_does_not_mutate_source(): void
    {
        $data=$this->data([['text'=>'ERD','report_points'=>[$this->point('ERD')]]]);$data['_processing']=['status'=>'running'];$copy=$data;
        $this->assertTrue(Coverage::calculate($data)['provisional']);$this->assertSame($copy,$data);
        $data['_processing']['status']='partial';
        $this->assertTrue(Coverage::calculate($data)['provisional']);
        $data['requirements_review']['status']='partial';
        $this->assertTrue(Coverage::calculate($data)['provisional']);
    }
}
