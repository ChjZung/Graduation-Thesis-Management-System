<?php

namespace App\Services;

class AiResultMerger
{
    public static function validProgress(mixed $value): bool
    {
        $keys = ['cong_viec', 'kho_khan', 'ke_hoach'];
        if (!is_array($value) || count($value) !== count($keys)) return false;
        foreach ($keys as $key) {
            if (!array_key_exists($key, $value)) return false;
            if ($value[$key] !== null && (!is_string($value[$key]) || trim($value[$key]) === '')) return false;
        }
        return true;
    }

    public function merge(array $old, array $new, bool $reviewOnly = false): array
    {
        $sameSource = empty($old['_source_hash']) || ($old['_source_hash'] === ($new['_source_hash'] ?? null));
        if (!self::validProgress($new['progress_v1'] ?? null)) {
            if (array_key_exists('progress_v1', $new)) $new['progress_v1_status'] = 'unavailable';
            unset($new['progress_v1'], $new['progress_display']);
        }
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
        $priorProgress = $old['progress_v1'] ?? null;
        $newProgress = $new['progress_v1'] ?? null;
        unset($merged['progress_v1'], $merged['progress_v1_status'], $merged['progress_display']);
        if (self::validProgress($newProgress) && (!$reviewOnly || !self::validProgress($priorProgress))
            && (!self::validProgress($priorProgress)
                || ($new['progress_v1_status'] ?? '') === 'complete'
                || ($old['progress_v1_status'] ?? '') !== 'complete')) {
            $merged['progress_v1'] = $newProgress;
            $merged['progress_v1_status'] = $new['progress_v1_status'] ?? 'partial';
            $merged['progress_display'] = $new['progress_display'] ?? [];
        } elseif (self::validProgress($priorProgress)) {
            $merged['progress_v1'] = $priorProgress;
            $merged['progress_v1_status'] = $old['progress_v1_status'] ?? 'partial';
            $merged['progress_display'] = $old['progress_display'] ?? [];
        } elseif (($new['progress_v1_status'] ?? '') === 'unavailable'
            || ($old['progress_v1_status'] ?? '') === 'unavailable') {
            $merged['progress_v1_status'] = 'unavailable';
        }
        if (!empty($new['source_navigation']) || !empty($old['source_navigation'])) {
            $merged['source_navigation'] = $new['source_navigation'] ?? $old['source_navigation'];
        }
        // Caption metadata is independent of summary/review completion.
        if (($new['caption_index']['status'] ?? '') === 'completed' &&
            ($new['_source_hash'] ?? null) === ($old['_source_hash'] ?? $new['_source_hash'] ?? null)) {
            $merged['caption_index'] = $new['caption_index'];
            if ($keepSummary) {
                $enrich = function(array $nodes, array $fresh) use (&$enrich): array {
                    $byId = array_column($fresh, null, 'id');
                    foreach ($nodes as &$node) {
                        $other = $byId[$node['id'] ?? ''] ?? null;
                        if (!$other) continue;
                        $node['related_assets'] = $other['related_assets'] ?? [];
                        $node['sections'] = empty($node['sections']) ? ($other['sections'] ?? []) :
                            $enrich($node['sections'], $other['sections'] ?? []);
                    }
                    unset($node);
                    return $nodes;
                };
                $merged['chapters'] = $enrich($merged['chapters'] ?? [], $new['chapters'] ?? []);
            }
        }
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
        foreach (['teacher_requirements','requirements_document','teacher_requirements_status','requirements_links_status','requirements_provenance'] as $field) {
            if (array_key_exists($field,$new)) $merged[$field]=$new[$field];
        }
        // The local content audit is independent of an incomplete LLM generation.
        $audit=$new['content_review'] ?? null;
        if (is_array($audit) && ($audit['status'] ?? '')==='completed' &&
            ($audit['source_hash'] ?? '')===($new['_source_hash'] ?? null) &&
            (empty($old['content_review']['coverage']['map_scope_complete']) || !empty($audit['coverage']['map_scope_complete']))) {
            $merged['content_review']=$audit;
        }
        if (isset($new['_source_hash'])) $merged['_source_hash'] = $new['_source_hash'];
        $merged = self::fillMissingReading($merged, $old);
        return AiRequirementResultPolicy::preserveVerified($old, $merged, $new);
    }

    public static function fillMissingReading(array $current, array $saved): array
    {
        $hash = $current['_source_hash'] ?? $current['source_identity']['source_hash'] ?? null;
        $version = $current['document_version_id'] ?? null;
        if (!$hash || !$version || $hash !== ($saved['_source_hash'] ?? $saved['source_identity']['source_hash'] ?? null)
            || $version !== ($saved['document_version_id'] ?? null)) return $current;

        if (empty($current['overall_summary']) && !empty($saved['overall_summary'])) {
            foreach (['overall_summary', 'overview_summary', 'document_content'] as $key) {
                if (empty($current[$key]) && !empty($saved[$key])) $current[$key] = $saved[$key];
            }
        }
        $current['chapters'] = self::fillReadingNodes($current['chapters'] ?? [], $saved['chapters'] ?? []);
        return $current;
    }

    private static function fillReadingNodes(array $current, array $saved): array
    {
        if (!$current) return $saved;
        $previous = array_column($saved, null, 'id');
        foreach ($current as &$node) {
            $old = $previous[$node['id'] ?? ''] ?? null;
            if (!$old || ($old['title'] ?? null) !== ($node['title'] ?? null)
                || ($old['start_page'] ?? null) !== ($node['start_page'] ?? null)
                || ($old['end_page'] ?? null) !== ($node['end_page'] ?? null)) continue;
            if (empty($node['chapter_reading']['paragraphs']) && !empty($old['chapter_reading']['paragraphs'])) {
                $node['chapter_reading'] = array_replace($old['chapter_reading'], ['status'=>'partial']);
                $node['items'] = $old['items'] ?? [];
                $node['summary'] = $old['summary'] ?? '';
            }
            $node['sections'] = self::fillReadingNodes($node['sections'] ?? [], $old['sections'] ?? []);
        }
        unset($node);
        return $current;
    }
}
