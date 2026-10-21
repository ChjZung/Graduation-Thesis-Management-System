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
];
