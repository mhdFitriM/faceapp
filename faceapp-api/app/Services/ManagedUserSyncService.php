<?php

namespace App\Services;

use App\Models\Device;
use App\Models\ManagedUser;
use App\Models\ManagedUserDeviceSync;
use Illuminate\Support\Collection;
use RuntimeException;
use Throwable;

class ManagedUserSyncService
{
    public function __construct(
        protected readonly GatewaySdkClient $gateway,
        protected readonly SystemSettingsService $settings,
    ) {}

    public function activeManagedDevices(): Collection
    {
        return Device::query()
            ->where('is_managed', true)
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();
    }

    public function syncUserAcrossActiveDevices(ManagedUser $user): array
    {
        $devices = $this->activeManagedDevices();

        if ($devices->isEmpty()) {
            throw new RuntimeException('No active managed devices are configured.');
        }

        return $devices
            ->map(fn (Device $device): array => $this->syncUserToDeviceWithResult($user, $device))
            ->all();
    }

    public function syncUserToDevice(ManagedUser $user, Device $device): ManagedUserDeviceSync
    {
        $client = $this->gateway->forDevice($device);
        $sync = ManagedUserDeviceSync::firstOrNew([
            'managed_user_id' => $user->id,
            'device_id' => $device->id,
        ]);

        $mergeResponse = $client->upsertPerson([
            'employee_id' => $user->employee_id,
            'name' => $user->name,
            'person_type' => $user->person_type ?: $device->person_type_default,
            'verify_style' => $user->verify_style ?: $device->verify_style_default,
            'ac_group_number' => $user->ac_group_number ?: $device->ac_group_number_default,
        ]);

        $verificationResponse = $this->verifyPersonSync($client, $user->employee_id);

        if (! $client->personExists($verificationResponse)) {
            $sync->fill([
                'sync_status' => 'failed',
                'gateway_person_response' => [
                    'merge_response' => $mergeResponse,
                    'verification_response' => $verificationResponse,
                ],
                'last_error_message' => 'Gateway did not confirm the user record.',
            ])->save();

            throw new RuntimeException('Gateway did not confirm the user record on device '.$device->display_name.'.');
        }

        $sync->fill([
            'sync_status' => 'synced',
            'last_synced_at' => now(),
            'last_error_message' => null,
            'gateway_person_response' => [
                'merge_response' => $mergeResponse,
                'verification_response' => $verificationResponse,
            ],
        ])->save();

        return $sync->fresh();
    }

    public function deleteUserAcrossActiveDevices(ManagedUser $user): array
    {
        $devices = $this->activeManagedDevices();

        return $devices
            ->map(fn (Device $device): array => $this->deleteUserFromDeviceWithResult($user, $device))
            ->all();
    }

    public function importUsersFromDevice(Device $device, int $pageLength = 20): array
    {
        $client = $this->gateway->forDevice($device);
        $page = 1;
        $results = [];
        $maxPages = 100;

        do {
            $response = $client->findPersonList($page, $pageLength);
            $people = $client->extractPersonList($response);

            foreach ($people as $person) {
                $results[] = $this->importUserFromPayload($device, $person);
            }

            $hasMore = $client->personListHasMore($response, $page, $pageLength, count($people));
            $page++;
        } while ($hasMore && $page <= $maxPages);

        return $results;
    }

    public function importUserRegistrationCallback(Device $device, array $payload): ManagedUserDeviceSync
    {
        return $this->importUserFromPayload($device, $payload, source: 'device_callback');
    }

    protected function syncUserToDeviceWithResult(ManagedUser $user, Device $device): array
    {
        try {
            $sync = $this->syncUserToDevice($user, $device);

            return [
                'device_id' => $device->id,
                'device_key' => $device->device_key,
                'device_name' => $device->display_name,
                'status' => 'synced',
                'sync' => $sync->toArray(),
            ];
        } catch (Throwable $exception) {
            return [
                'device_id' => $device->id,
                'device_key' => $device->device_key,
                'device_name' => $device->display_name,
                'status' => 'failed',
                'error' => $exception->getMessage(),
            ];
        }
    }

    protected function deleteUserFromDeviceWithResult(ManagedUser $user, Device $device): array
    {
        $client = $this->gateway->forDevice($device);
        $sync = ManagedUserDeviceSync::firstOrNew([
            'managed_user_id' => $user->id,
            'device_id' => $device->id,
        ]);

        try {
            $response = $client->deletePerson($user->employee_id);

            $sync->fill([
                'sync_status' => 'deleted',
                'face_status' => 'deleted',
                'last_error_message' => null,
                'gateway_person_response' => $response,
            ])->save();

            return [
                'device_id' => $device->id,
                'device_key' => $device->device_key,
                'device_name' => $device->display_name,
                'status' => 'deleted',
            ];
        } catch (Throwable $exception) {
            $sync->fill([
                'sync_status' => 'failed',
                'last_error_message' => $exception->getMessage(),
            ])->save();

            return [
                'device_id' => $device->id,
                'device_key' => $device->device_key,
                'device_name' => $device->display_name,
                'status' => 'failed',
                'error' => $exception->getMessage(),
            ];
        }
    }

    protected function verifyPersonSync(GatewaySdkClient $client, string $employeeId): array
    {
        $attempts = max(1, $this->settings->personVerifyRetries());
        $delayMilliseconds = max(0, $this->settings->personVerifyDelayMs());
        $lastResponse = [];

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            $lastResponse = $client->findPerson($employeeId);

            if ($client->personExists($lastResponse)) {
                return $lastResponse;
            }

            if ($attempt < $attempts && $delayMilliseconds > 0) {
                usleep($delayMilliseconds * 1000);
            }
        }

        return $lastResponse;
    }

    protected function importUserFromPayload(Device $device, array $payload, string $source = 'device_import'): ManagedUserDeviceSync
    {
        $employeeId = trim((string) ($payload['sn'] ?? $payload['personSn'] ?? ''));

        if ($employeeId === '') {
            throw new RuntimeException('The device user payload does not contain an employee number.');
        }

        $existingUser = ManagedUser::query()->where('employee_id', $employeeId)->first();
        $incomingName = trim((string) ($payload['name'] ?? ''));
        $resolvedName = $existingUser && filled($existingUser->name)
            ? $existingUser->name
            : ($incomingName !== '' ? $incomingName : $employeeId);

        $user = ManagedUser::query()->updateOrCreate(
            ['employee_id' => $employeeId],
            [
                'name' => $resolvedName,
                'role' => $this->preferExisting($existingUser?->role, $payload['role'] ?? null),
                'department' => $this->preferExisting($existingUser?->department, $payload['department'] ?? null),
                'access_level' => $this->preferExisting($existingUser?->access_level, $payload['accessLevel'] ?? null),
                'mobile' => $this->preferExisting($existingUser?->mobile, $payload['mobile'] ?? null),
                'card_no' => $this->preferExisting($existingUser?->card_no, $payload['cardNo'] ?? null),
                'id_card' => $this->preferExisting($existingUser?->id_card, $payload['idCard'] ?? null),
                'voucher_code' => $this->preferExisting($existingUser?->voucher_code, $payload['voucherCode'] ?? null),
                'verify_pwd' => $this->preferExisting($existingUser?->verify_pwd, $payload['verifyPwd'] ?? null),
                'person_type' => $this->intOrFallback($payload['personType'] ?? null, $existingUser?->person_type, $device->person_type_default),
                'verify_style' => $this->intOrFallback($payload['verifyStyle'] ?? null, $existingUser?->verify_style, $device->verify_style_default),
                'ac_group_number' => $this->intOrFallback($payload['acGroupNumber'] ?? null, $existingUser?->ac_group_number, $device->ac_group_number_default),
                'is_active' => $existingUser?->is_active ?? true,
            ],
        );

        $sync = ManagedUserDeviceSync::query()->firstOrNew([
            'managed_user_id' => $user->id,
            'device_id' => $device->id,
        ]);

        $sync->fill([
            'sync_status' => 'synced',
            'face_status' => $sync->face_status,
            'last_synced_at' => now(),
            'last_error_message' => null,
            'gateway_person_response' => [
                'source' => $source,
                'payload' => $payload,
            ],
        ])->save();

        return $sync->fresh();
    }

    protected function preferExisting(mixed $existing, mixed $incoming): ?string
    {
        $existing = trim((string) ($existing ?? ''));

        if ($existing !== '') {
            return $existing;
        }

        $incoming = trim((string) ($incoming ?? ''));

        return $incoming !== '' ? $incoming : null;
    }

    protected function intOrFallback(mixed $incoming, mixed $existing, int $fallback): int
    {
        if (is_numeric($incoming)) {
            return (int) $incoming;
        }

        if (is_numeric($existing)) {
            return (int) $existing;
        }

        return $fallback;
    }
}
