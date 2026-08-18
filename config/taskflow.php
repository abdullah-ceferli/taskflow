<?php

return [
    'attachments' => [
        'workspace_quota_bytes' => 100 * 1024 * 1024,
        'max_files_per_request' => 10,
    ],
    'performance' => [
        'telemetry_enabled' => true,
        'slow_query_ms' => 250,
        'sample_limit' => 200,
        'dashboard_cache_seconds' => 30,
    ],
    'activity_retention' => [
        'enabled' => false,
        'days' => 365,
        'chunk_size' => 1000,
        'disk' => 'local',
    ],
    'release' => env('TASKFLOW_RELEASE', 'local'),
    'features' => [
        // Add boolean, environment-backed flags here. Flags default to disabled.
    ],
    'operations' => [
        'token' => env('TASKFLOW_OPERATIONS_TOKEN'),
        'owner' => env('TASKFLOW_SLO_OWNER', 'platform-owner'),
        'runbook_url' => env('TASKFLOW_RUNBOOK_URL', '/docs/OPERATIONS_RUNBOOK.md'),
        'thresholds' => [
            'request_p95_ms' => (float) env('TASKFLOW_SLO_REQUEST_P95_MS', 1000),
            'request_error_rate_percent' => (float) env('TASKFLOW_SLO_ERROR_RATE_PERCENT', 2),
            'oldest_queue_age_seconds' => (int) env('TASKFLOW_SLO_QUEUE_AGE_SECONDS', 300),
            'failed_jobs' => (int) env('TASKFLOW_SLO_FAILED_JOBS', 0),
            'failed_notifications' => (int) env('TASKFLOW_SLO_FAILED_NOTIFICATIONS', 0),
            'failed_webhooks' => (int) env('TASKFLOW_SLO_FAILED_WEBHOOKS', 0),
        ],
    ],
];
