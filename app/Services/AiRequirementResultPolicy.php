<?php

namespace App\Services;

/** Pure result policy. No retrieval, filesystem access, database writes or AI. */
class AiRequirementResultPolicy
{
    public const OUTPUT_GUARD_VERSION = 'review-output-guard-1.3';
    private const FIELDS = ['teacher_requirements', 'requirements_document', 'teacher_requirements_status',
        'requirements_links_status', 'requirements_review', 'requirements_provenance'];

    public static function contextHash(array $context): string
    {
        ksort($context);
        return hash('sha256', json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    public static function forContext(array $data, array $context): array
    {
        $bound = $data['requirements_provenance']['context_hash'] ?? $data['summary_scope']['context_hash'] ?? null;
        // Unknown legacy bindings are labelled, never upgraded to verified results.
        if (!$bound) {
            $data['requirements_binding_status'] = 'legacy_unverified';
            return $data;
        }
        if (!hash_equals((string) $bound, self::contextHash($context))) {
            foreach (self::FIELDS as $field) unset($data[$field]);
            $data['teacher_requirements'] = [];
            $data['teacher_requirements_status'] = 'pending';
            $data['requirements_links_status'] = 'pending';
            $data['requirements_review'] = ['status'=>'pending', 'items'=>[],
                'note'=>'Yêu cầu đã thay đổi. Cần đối chiếu lại; bản tóm tắt báo cáo được giữ nguyên.'];
            $data['requirements_binding_status'] = 'stale';
        } else {
            $data['requirements_binding_status'] = 'current';
        }
        return $data;
    }

    private static function verified(array $data): bool
    {
        if (($data['requirements_provenance']['method'] ?? '') === 'verified_pdf_review') return true;
        // Legacy direct-review output carries its verifier policy; mode alone is insufficient.
        return !empty($data['requirements_review']['policy_version']);
    }

    public static function preserveVerified(array $old, array $merged, array $incoming): array
    {
        $oldHash = $old['_source_hash'] ?? null;
        $newHash = $incoming['_source_hash'] ?? null;
        $oldContext = $old['requirements_provenance']['context_hash'] ?? $old['summary_scope']['context_hash'] ?? null;
        $newContext = $incoming['requirements_provenance']['context_hash'] ?? $incoming['summary_scope']['context_hash'] ?? null;
        if ($oldHash && $oldHash === $newHash && $oldContext && $oldContext === $newContext &&
            self::verified($old) && !self::verified($incoming)) {
            foreach (self::FIELDS as $field) {
                unset($merged[$field]);
                if (array_key_exists($field, $old)) $merged[$field] = $old[$field];
            }
        }
        return $merged;
    }
}
