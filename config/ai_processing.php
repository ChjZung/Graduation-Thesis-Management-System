<?php

return [
    'connection' => 'ai',
    'queue' => 'ai-reports',
    // One scope for every key belonging to this Google project on this deployment.
    'quota_scope' => env('GEMINI_QUOTA_SCOPE', 'default-gemini-project'),
    'rpm' => (int) env('GEMINI_SAFE_RPM', 4),
    'lite_rpm' => (int) env('GEMINI_LITE_SAFE_RPM', 12),
    'tpm' => (int) env('GEMINI_SAFE_TPM', 200000),
    'max_passes' => 20,
    'pipeline_version' => 'pdf-lab-v1.7-verified-requirement-review',
    'pdf_engine' => 'pdf_lab',
    'lab' => [
        'review_engine' => 'verified_v2',
        'input_budget' => 8500,
        'max_output_tokens' => 16384,
        'max_generation_calls' => 5,
        'pass_seconds' => 260,
    ],
    'count_tokens' => true,
    'max_request_tokens' => 12000,
    // Document text budget, separate from provider quotas. Counts are conservative estimates.
    'summary' => [
        'target_tokens' => 2500,
        'overlap_tokens' => 200,
        'prompt_reserve_tokens' => 1500,
        'max_input_tokens' => 12000,
    ],
];
