<?php

namespace Tests\Unit;

use App\Services\AiResultMerger;
use PHPUnit\Framework\TestCase;

class AiReportProgressContractTest extends TestCase
{
    public function test_invalid_progress_does_not_replace_the_prior_result_for_the_same_source(): void
    {
        $prior = ['cong_viec' => 'Nhóm đã nộp đề cương.', 'kho_khan' => null, 'ke_hoach' => null];
        $old = ['_source_hash' => 'source-a', 'progress_v1' => $prior, 'progress_v1_status' => 'complete'];
        $merger = new AiResultMerger;

        foreach ([null, ['cong_viec' => []], $prior + ['extra' => null], array_replace($prior, ['ke_hoach' => 42])] as $invalid) {
            $new = ['_source_hash' => 'source-a', 'progress_v1' => $invalid, 'chapters' => ['retained']];
            $merged = $merger->merge($old, $new);
            self::assertSame($prior, $merged['progress_v1']);
            self::assertSame('complete', $merged['progress_v1_status']);
            self::assertSame(['retained'], $merged['chapters']);
        }
    }

    public function test_progress_is_bound_to_the_source_and_partial_cannot_replace_complete(): void
    {
        $prior = ['cong_viec' => 'Đã nộp đề cương.', 'kho_khan' => null, 'ke_hoach' => null];
        $new = array_fill_keys(array_keys($prior), null);
        $old = ['_source_hash' => 'source-a', 'progress_v1' => $prior, 'progress_v1_status' => 'complete'];
        $merger = new AiResultMerger;

        self::assertSame($prior, $merger->merge($old, [
            '_source_hash' => 'source-a', 'progress_v1' => $new, 'progress_v1_status' => 'partial',
        ])['progress_v1']);
        self::assertSame($new, $merger->merge($old, [
            '_source_hash' => 'source-b', 'progress_v1' => $new, 'progress_v1_status' => 'complete',
        ])['progress_v1']);
        self::assertArrayNotHasKey('progress_v1', $merger->merge($old, ['_source_hash' => 'source-b']));
        self::assertSame($prior, $merger->merge($old, ['_source_hash' => 'source-a'], true)['progress_v1']);
    }
}
