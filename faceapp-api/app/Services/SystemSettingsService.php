<?php

namespace App\Services;

use App\Models\SystemSetting;

class SystemSettingsService
{
    public function current(): SystemSetting
    {
        return SystemSetting::singleton();
    }

    public function gatewayBaseUrl(): string
    {
        return $this->value('gateway_base_url') ?: rtrim((string) config('gateway.base_url', 'http://127.0.0.1:8190/api'), '/');
    }

    public function publicStorageBaseUrl(): string
    {
        $fallback = rtrim((string) config('gateway.upload.public_base_url', ''), '/');

        return $this->value('public_storage_base_url') ?: $fallback;
    }

    public function gatewayImageBaseUrl(): string
    {
        $fallback = rtrim((string) config('gateway.upload.gateway_base_url', ''), '/');

        return $this->value('gateway_image_base_url') ?: $fallback;
    }

    public function gatewayCallbackBaseUrl(): string
    {
        $fallback = rtrim((string) config('gateway.monitoring.callback_base_url', ''), '/');

        return $this->value('gateway_callback_base_url') ?: $fallback;
    }

    public function heartbeatIntervalSeconds(): int
    {
        return (int) ($this->current()->heartbeat_interval_seconds ?: config('gateway.monitoring.heartbeat_interval_seconds', 60));
    }

    public function onlineWindowSeconds(): int
    {
        return (int) ($this->current()->online_window_seconds ?: config('gateway.monitoring.online_window_seconds', 180));
    }

    public function personVerifyRetries(): int
    {
        return (int) ($this->current()->person_verify_retries ?: config('gateway.verification.person_retries', 5));
    }

    public function personVerifyDelayMs(): int
    {
        return (int) ($this->current()->person_verify_delay_ms ?: config('gateway.verification.person_delay_milliseconds', 1000));
    }

    public function faceVerifyRetries(): int
    {
        return (int) ($this->current()->face_verify_retries ?: config('gateway.verification.retries', 5));
    }

    public function faceVerifyDelayMs(): int
    {
        return (int) ($this->current()->face_verify_delay_ms ?: config('gateway.verification.delay_milliseconds', 1500));
    }

    protected function value(string $attribute): string
    {
        return trim((string) $this->current()->getAttribute($attribute));
    }
}
