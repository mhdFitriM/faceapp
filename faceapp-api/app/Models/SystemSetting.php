<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'gateway_base_url',
        'public_storage_base_url',
        'gateway_image_base_url',
        'gateway_callback_base_url',
        'heartbeat_interval_seconds',
        'online_window_seconds',
        'person_verify_retries',
        'person_verify_delay_ms',
        'face_verify_retries',
        'face_verify_delay_ms',
    ];

    public static function singleton(): self
    {
        return static::query()->firstOrCreate(['id' => 1], [
            'gateway_base_url' => config('gateway.base_url'),
            'public_storage_base_url' => config('gateway.upload.public_base_url'),
            'gateway_image_base_url' => config('gateway.upload.gateway_base_url'),
            'gateway_callback_base_url' => config('gateway.monitoring.callback_base_url'),
            'heartbeat_interval_seconds' => config('gateway.monitoring.heartbeat_interval_seconds', 60),
            'online_window_seconds' => config('gateway.monitoring.online_window_seconds', 180),
            'person_verify_retries' => config('gateway.verification.person_retries', 5),
            'person_verify_delay_ms' => config('gateway.verification.person_delay_milliseconds', 1000),
            'face_verify_retries' => config('gateway.verification.retries', 5),
            'face_verify_delay_ms' => config('gateway.verification.delay_milliseconds', 1500),
        ]);
    }
}
