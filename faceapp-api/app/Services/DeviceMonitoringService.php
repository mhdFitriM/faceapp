<?php

namespace App\Services;

use App\Models\Device;
use App\Models\DeviceEvent;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use RuntimeException;
use Throwable;

class DeviceMonitoringService
{
    public function __construct(
        protected readonly ManagedUserSyncService $userSyncs,
    ) {}

    public function recordHeartbeat(array $payload): Device
    {
        $device = Device::firstOrNew([
            'device_key' => $this->deviceKey($payload),
        ]);

        $heartbeatAt = $this->resolveTimestamp($payload['time'] ?? null) ?? now()->toImmutable();

        $device->fill([
            'last_ip' => $this->nullableString($payload['ip'] ?? null),
            'last_version' => $this->nullableString($payload['version'] ?? null),
            'person_count' => $this->nullableInt($payload['personCount'] ?? null),
            'face_count' => $this->nullableInt($payload['faceCount'] ?? null),
            'free_disk_space' => $this->nullableString($payload['freeDiskSpace'] ?? null),
            'last_seen_at' => $heartbeatAt,
            'last_heartbeat_payload' => $payload,
        ]);

        $device->save();

        DeviceEvent::create([
            'device_key' => $device->device_key,
            'event_type' => 'heartbeat',
            'event_time' => $heartbeatAt,
            'payload' => $payload,
        ]);

        return $device->fresh();
    }

    public function recordAccessRecord(array $payload): DeviceEvent
    {
        $eventTime = $this->resolveTimestamp($payload['recordTime'] ?? null)
            ?? $this->resolveTimestamp($payload['time'] ?? null)
            ?? now()->toImmutable();

        $device = $this->touchDevice($payload, [
            'last_record_at' => $eventTime,
            'last_record_payload' => $payload,
        ]);

        return $this->storeEvent(
            eventType: 'access_record',
            payload: $payload,
            eventTime: $eventTime,
            eventUid: $this->nullableString($payload['recordId'] ?? null),
            personSn: $this->nullableString($payload['personSn'] ?? null),
            resultFlag: $this->nullableInt($payload['resultFlag'] ?? null),
            deviceKey: $device->device_key,
        );
    }

    public function recordPersonRegistration(array $payload): DeviceEvent
    {
        $eventTime = $this->resolveTimestamp($payload['time'] ?? null) ?? now()->toImmutable();

        $device = $this->touchDevice($payload);

        try {
            $this->userSyncs->importUserRegistrationCallback($device, $payload);
        } catch (Throwable $exception) {
            report($exception);
        }

        return $this->storeEvent(
            eventType: 'person_registration',
            payload: $payload,
            eventTime: $eventTime,
            eventUid: null,
            personSn: $this->nullableString($payload['personSn'] ?? null),
            resultFlag: null,
            deviceKey: $device->device_key,
        );
    }

    protected function touchDevice(array $payload, array $extra = []): Device
    {
        $device = Device::firstOrNew([
            'device_key' => $this->deviceKey($payload),
        ]);

        $updates = array_filter([
            'last_ip' => $this->nullableString($payload['ip'] ?? null),
            'last_seen_at' => $this->resolveTimestamp($payload['time'] ?? null),
            ...$extra,
        ], static fn (mixed $value): bool => $value !== null);

        if ($updates !== []) {
            $device->fill($updates)->save();
        } elseif (! $device->exists) {
            $device->save();
        }

        return $device->fresh();
    }

    protected function storeEvent(
        string $eventType,
        array $payload,
        CarbonImmutable $eventTime,
        ?string $eventUid,
        ?string $personSn,
        ?int $resultFlag,
        string $deviceKey,
    ): DeviceEvent {
        $event = $eventUid
            ? DeviceEvent::firstOrNew([
                'device_key' => $deviceKey,
                'event_type' => $eventType,
                'event_uid' => $eventUid,
            ])
            : new DeviceEvent([
                'device_key' => $deviceKey,
                'event_type' => $eventType,
            ]);

        $event->fill([
            'person_sn' => $personSn,
            'result_flag' => $resultFlag,
            'event_time' => $eventTime,
            'payload' => $payload,
        ]);

        $event->save();

        return $event;
    }

    protected function deviceKey(array $payload): string
    {
        $deviceKey = trim((string) Arr::get($payload, 'deviceKey', ''));

        if ($deviceKey === '') {
            throw new RuntimeException('Callback payload is missing deviceKey.');
        }

        return $deviceKey;
    }

    protected function resolveTimestamp(mixed $value): ?CarbonImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $timestamp = (string) $value;

            return strlen($timestamp) >= 13
                ? CarbonImmutable::createFromTimestampMs((int) $timestamp)
                : CarbonImmutable::createFromTimestamp((int) $timestamp);
        }

        return CarbonImmutable::parse((string) $value);
    }

    protected function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    protected function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
