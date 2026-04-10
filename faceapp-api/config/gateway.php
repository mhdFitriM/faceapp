<?php

return [
    'base_url' => rtrim((string) env('GATEWAY_BASE_URL', 'http://127.0.0.1:8190/api'), '/'),
    'timeout_seconds' => (int) env('GATEWAY_TIMEOUT_SECONDS', 10),
    'device_key' => env('GATEWAY_DEVICE_KEY'),
    'secret' => env('GATEWAY_SECRET'),

    'defaults' => [
        'person_type' => (int) env('GATEWAY_DEFAULT_PERSON_TYPE', 1),
        'verify_style' => (int) env('GATEWAY_DEFAULT_VERIFY_STYLE', 1),
        'ac_group_number' => (int) env('GATEWAY_DEFAULT_AC_GROUP_NUMBER', 0),
        'photo_quality' => (int) env('GATEWAY_DEFAULT_PHOTO_QUALITY', 1),
    ],

    'upload' => [
        'disk' => env('GATEWAY_UPLOAD_DISK', 'public'),
        'directory' => trim((string) env('GATEWAY_UPLOAD_DIR', 'face-uploads'), '/'),
        'public_base_url' => rtrim((string) env('FACE_PUBLIC_BASE_URL', ''), '/'),
        'gateway_base_url' => rtrim((string) env('GATEWAY_IMAGE_BASE_URL', ''), '/'),
    ],

    'monitoring' => [
        'callback_base_url' => rtrim((string) env('GATEWAY_CALLBACK_BASE_URL', ''), '/'),
        'heartbeat_interval_seconds' => (int) env('GATEWAY_HEARTBEAT_INTERVAL_SECONDS', 60),
        'online_window_seconds' => (int) env('GATEWAY_ONLINE_WINDOW_SECONDS', 180),
    ],

    'verification' => [
        'person_retries' => (int) env('GATEWAY_PERSON_VERIFY_RETRIES', 5),
        'person_delay_milliseconds' => (int) env('GATEWAY_PERSON_VERIFY_DELAY_MS', 1000),
        'retries' => (int) env('GATEWAY_VERIFY_RETRIES', 5),
        'delay_milliseconds' => (int) env('GATEWAY_VERIFY_DELAY_MS', 1500),
    ],
];
