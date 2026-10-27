<?php

namespace App\Services;

class AiResultMerger
{
    public function merge(array $old, array $new, bool $reviewOnly = false): array
    {
        $sameSource = empty($old['_source_hash']) || ($old['_source_hash'] === ($new['_source_hash'] ?? null));
        if (!$sameSource) return $new;
        $oldAnalysis = $old['analysis'] ?? [];
        $newAnalysis = $new['analysis'] ?? [];
        $sameScope = ($old['summary_scope']['context_hash'] ?? '') === ($new['summary_scope']['context_hash'] ?? '');
        $samePipeline = ($old['schema_version'] ?? 0) === ($new['schema_version'] ?? 0) && $sameScope;
        if (($old['schema_version'] ?? 0) === 6 && ($new['schema_version'] ?? 0) === 6) {
            $samePipeline = ($oldAnalysis['token_settings'] ?? []) === ($newAnalysis['token_settings'] ?? []);
        }
        $noNewContent = ($newAnalysis['source'] ?? '') === 'system_fallback' ||
            ((array_key_exists('total_units', $newAnalysis) || array_key_exists('total_pages', $newAnalysis))
                && empty($newAnalysis['total_units']) && empty($newAnalysis['total_pages']));
        $keepSummary = $reviewOnly || ($oldAnalysis && $noNewContent) || ($samePipeline && $oldAnalysis && (
            (($oldAnalysis['status'] ?? '') === 'completed' && ($newAnalysis['status'] ?? '') !== 'completed') ||
            (int) ($oldAnalysis['analyzed_chunks'] ?? 0) > (int) ($newAnalysis['analyzed_chunks'] ?? 0) ||
            (empty($newAnalysis['total_units']) && empty($newAnalysis['total_pages']))
        ));
        $merged = $keepSummary ? $old : $new;
        $previous = $old['requirements_review'] ?? null;
        $review = $new['requirements_review'] ?? null;
        if (is_array($review)) {
            if (is_array($previous) && !empty($previous['context_hash']) && $previous['context_hash'] === ($review['context_hash'] ?? null) && ($previous['schema_version'] ?? 0) === ($review['schema_version'] ?? 0)) {
                $prior = [];
                foreach ($previous['items'] ?? [] as $item) $prior[($item['id'] ?? '').'|'.($item['requirement'] ?? '')] = $item;
                $items = $review['items'] ?? [];
                foreach ($items as &$item) {
                    $before = $prior[($item['id'] ?? '').'|'.($item['requirement'] ?? '')] ?? null;
                    $verified = $before && count(array_filter($before['evidence'] ?? [], fn ($e) => !empty($e['verified']) && !empty($e['relevance_verified']))) > 0;
                    if ($verified && ($item['citation_audit_status'] ?? '') === 'failed' && in_array($before['status'] ?? '', ['met','partial'])) $item = $before;
                }
                unset($item);
                $review['items'] = $items;
                $oldVerified = count(array_filter($previous['items'] ?? [], fn ($i) => in_array($i['status'] ?? '',['met','partial']) && ($i['citation_audit_status'] ?? '') === 'completed'));
                $newVerified = count(array_filter($review['items'] ?? [], fn ($i) => in_array($i['status'] ?? '',['met','partial']) && ($i['citation_audit_status'] ?? '') === 'completed'));
                $oldCoverage = (int) ($previous['coverage']['analyzed_batches'] ?? 0);
                $newCoverage = (int) ($review['coverage']['analyzed_batches'] ?? 0);
                if ((($previous['status'] ?? '') === 'completed' && ($review['status'] ?? '') !== 'completed') ||
                    ($oldVerified >= $newVerified && $oldCoverage > $newCoverage && ($review['status'] ?? '') === 'service_error')) $review = $previous;
                $counts = array_fill_keys(['met','partial','not_found','insufficient_evidence'], 0);
                foreach ($review['items'] ?? [] as $item) $counts[array_key_exists($item['status'] ?? '',$counts) ? $item['status'] : 'insufficient_evidence']++;
                $review['counts'] = $counts;
                $review['summary'] = sprintf('Đã đối chiếu %d yêu cầu: %d đáp ứng, %d đáp ứng một phần, %d chưa tìm thấy trong PDF, %d chưa đủ bằng chứng.', array_sum($counts), ...array_values($counts));
            }
            $merged['requirements_review'] = $review;
        } elseif ($previous) {
            $merged['requirements_review'] = $previous;
        }
        // Keep the new brief summary even when a stronger prior document result is retained.
        foreach (['teacher_requirements','requirements_document','teacher_requirements_status'] as $field) {
            if (array_key_exists($field,$new)) $merged[$field]=$new[$field];
        }
        if (isset($new['_source_hash'])) $merged['_source_hash'] = $new['_source_hash'];
        return $merged;
    }
}
