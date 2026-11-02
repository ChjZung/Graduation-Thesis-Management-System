<?php
namespace Tests\Unit;
use App\Services\AiResultMerger;
use App\Services\AiSummaryService;
use PHPUnit\Framework\TestCase;
class AiCaptionMergeTest extends TestCase
{
    public function test_caption_survives_formatting_and_review_only_merge(): void
    {
        $caption=['version'=>'caption-text-1','status'=>'completed','assets'=>[]];
        $new=['schema_version'=>6,'_source_hash'=>'hash','caption_index'=>$caption,
            'chapters'=>[['id'=>'C','related_assets'=>[['label'=>'Figure 1']], 'sections'=>[['id'=>'S']]]]];
        $formatted=(new AiSummaryService)->formatSummarizeResult(['data'=>$new]);
        $this->assertSame($caption,$formatted['data']['caption_index']);
        $old=['schema_version'=>6,'_source_hash'=>'hash','chapters'=>[['id'=>'C','summary'=>'Keep original','sections'=>[]]]];
        $merged=(new AiResultMerger)->merge($old,$new,true);
        $this->assertSame('Keep original',$merged['chapters'][0]['summary']);
        $this->assertSame('S',$merged['chapters'][0]['sections'][0]['id']);
        $this->assertSame($caption,$merged['caption_index']);
        $different=(new AiResultMerger)->merge($old,array_replace($new,['_source_hash'=>'other']),true);
        $this->assertArrayNotHasKey('summary',$different['chapters'][0]);
    }
}
