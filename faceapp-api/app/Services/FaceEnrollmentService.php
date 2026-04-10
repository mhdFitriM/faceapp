<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Enrollment;
use App\Models\ManagedUser;
use App\Models\ManagedUserDeviceSync;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class FaceEnrollmentService
{
    public function __construct(
        private readonly GatewaySdkClient $gateway,
        private readonly ManagedUserSyncService $userSyncs,
        private readonly SystemSettingsService $settings,
    ) {}

    public function enroll(array $payload): Enrollment
    {
        [$imageBase64, $extension] = $this->extractImagePayload($payload['photo_data_url']);

        $managedUser = $this->resolveManagedUser($payload);
        $devices = $this->userSyncs->activeManagedDevices();

        if ($devices->isEmpty()) {
            throw new RuntimeException('No active managed devices are configured.');
        }

        $enrollment = Enrollment::create([
            'managed_user_id' => $managedUser->id,
            'employee_id' => $managedUser->employee_id,
            'name' => $managedUser->name,
            'device_key' => $devices->pluck('device_key')->implode(','),
            'status' => 'pending',
        ]);

        try {
            $photoPath = $this->storePhoto($managedUser->employee_id, $imageBase64, $extension);
            $photoPublicUrl = $this->storageUrl($photoPath, $this->settings->publicStorageBaseUrl());
            $gatewayImageUrl = $this->storageUrl($photoPath, $this->settings->gatewayImageBaseUrl());

            $managedUser->forceFill([
                'photo_path' => $photoPath,
                'photo_public_url' => $photoPublicUrl,
            ])->save();

            $enrollment->forceFill([
                'photo_path' => $photoPath,
                'photo_public_url' => $photoPublicUrl,
            ])->save();

            $results = $this->syncFaceAcrossDevices(
                user: $managedUser,
                devices: $devices,
                imageUrl: $gatewayImageUrl,
                imageBase64: $imageBase64,
                photoQuality: (int) ($payload['photo_quality'] ?? 1),
            );

            $successCount = collect($results)->where('status', 'verified')->count();
            $status = match (true) {
                $successCount === count($results) => 'verified',
                $successCount > 0 => 'partial',
                default => 'failed',
            };

            $firstResult = collect($results)->first();
            $failedMessages = collect($results)
                ->where('status', '!=', 'verified')
                ->map(fn (array $result): string => ($result['device_name'] ?? $result['device_key']).': '.($result['error'] ?? 'Unknown error'))
                ->values()
                ->all();

            $enrollment->forceFill([
                'status' => $status,
                'gateway_person_status' => $status === 'verified' ? 'synced' : ($successCount > 0 ? 'partial' : 'failed'),
                'gateway_face_status' => $status === 'verified' ? 'verified' : ($successCount > 0 ? 'partial' : 'failed'),
                'gateway_person_response' => $firstResult['person_response'] ?? null,
                'gateway_face_response' => $firstResult['face_response'] ?? null,
                'verification_response' => $firstResult['verification_response'] ?? null,
                'sync_results' => $results,
                'error_message' => $failedMessages !== [] ? implode(' | ', $failedMessages) : null,
                'enrolled_at' => $successCount > 0 ? now() : null,
            ])->save();

            if ($successCount > 0) {
                $managedUser->forceFill([
                    'last_enrolled_at' => now(),
                ])->save();
            }

            if ($successCount === 0) {
                throw new RuntimeException($enrollment->error_message ?: 'Face enrollment did not complete on every active device.');
            }

            return $enrollment->fresh();
        } catch (Throwable $exception) {
            $enrollment->forceFill([
                'status' => $enrollment->status === 'pending' ? 'failed' : $enrollment->status,
                'error_message' => $exception->getMessage(),
            ])->save();

            report($exception);

            throw $exception;
        }
    }

    protected function syncFaceAcrossDevices(
        ManagedUser $user,
        Collection $devices,
        string $imageUrl,
        string $imageBase64,
        int $photoQuality,
    ): array {
        return $devices->map(function (Device $device) use ($user, $imageUrl, $imageBase64, $photoQuality): array {
            $client = $this->gateway->forDevice($device);
            $sync = ManagedUserDeviceSync::firstOrNew([
                'managed_user_id' => $user->id,
                'device_id' => $device->id,
            ]);

            $personResponse = null;
            $faceResponse = null;
            $verificationResponse = null;

            try {
                $sync = $this->userSyncs->syncUserToDevice($user, $device);
                $personResponse = $sync->gateway_person_response;

                $faceResponse = $client->mergeFace(
                    $user->employee_id,
                    $imageUrl,
                    $imageBase64,
                    $photoQuality > -1 ? $photoQuality : $device->photo_quality_default,
                );

                $verificationResponse = $this->verifyFaceUpload($client, $user->employee_id);

                if (! $client->faceExists($verificationResponse)) {
                    throw new RuntimeException('Gateway did not confirm the uploaded face.');
                }

                $sync->forceFill([
                    'sync_status' => 'synced',
                    'face_status' => 'verified',
                    'last_face_synced_at' => now(),
                    'last_error_message' => null,
                    'gateway_face_response' => $faceResponse,
                    'verification_response' => $verificationResponse,
                ])->save();

                return [
                    'device_id' => $device->id,
                    'device_key' => $device->device_key,
                    'device_name' => $device->display_name,
                    'status' => 'verified',
                    'person_response' => $personResponse,
                    'face_response' => $faceResponse,
                    'verification_response' => $verificationResponse,
                ];
            } catch (Throwable $exception) {
                $sync->forceFill([
                    'sync_status' => $sync->sync_status ?: 'failed',
                    'face_status' => 'failed',
                    'last_error_message' => $exception->getMessage(),
                    'gateway_face_response' => $faceResponse,
                    'verification_response' => $verificationResponse,
                ])->save();

                return [
                    'device_id' => $device->id,
                    'device_key' => $device->device_key,
                    'device_name' => $device->display_name,
                    'status' => 'failed',
                    'person_response' => $personResponse,
                    'face_response' => $faceResponse,
                    'verification_response' => $verificationResponse,
                    'error' => $exception->getMessage(),
                ];
            }
        })->all();
    }

    protected function resolveManagedUser(array $payload): ManagedUser
    {
        $managedUserId = $payload['managed_user_id'] ?? null;

        if ($managedUserId) {
            return ManagedUser::query()->findOrFail($managedUserId);
        }

        $employeeId = trim((string) ($payload['employee_id'] ?? ''));
        $name = trim((string) ($payload['name'] ?? ''));

        if ($employeeId === '' || $name === '') {
            throw new RuntimeException('A managed user or employee_id and name are required for enrollment.');
        }

        return ManagedUser::query()->updateOrCreate(
            ['employee_id' => $employeeId],
            [
                'name' => $name,
                'person_type' => (int) ($payload['person_type'] ?? 1),
                'verify_style' => (int) ($payload['verify_style'] ?? 1),
                'ac_group_number' => (int) ($payload['ac_group_number'] ?? 0),
                'is_active' => true,
            ],
        );
    }

    protected function extractImagePayload(string $dataUrl): array
    {
        if (! preg_match('/^data:image\/(?P<extension>jpeg|jpg|png);base64,(?P<data>.+)$/', $dataUrl, $matches)) {
            throw new RuntimeException('Unsupported image payload received from FaceApp.');
        }

        $imageBase64 = $matches['data'];
        $binary = base64_decode($imageBase64, true);

        if ($binary === false) {
            throw new RuntimeException('Failed to decode the captured image.');
        }

        $extension = $matches['extension'] === 'jpeg' ? 'jpg' : $matches['extension'];

        return [$imageBase64, $extension];
    }

    protected function storePhoto(string $employeeId, string $imageBase64, string $extension): string
    {
        $binary = base64_decode($imageBase64, true);

        if ($binary === false) {
            throw new RuntimeException('Failed to decode the image before upload.');
        }

        $directory = trim((string) config('gateway.upload.directory', 'face-uploads'), '/');
        $fileName = Str::slug($employeeId).'-'.Str::lower(Str::random(8)).'.'.$extension;
        $path = $directory.'/'.$fileName;

        Storage::disk((string) config('gateway.upload.disk', 'public'))->put($path, $binary);

        return $path;
    }

    protected function storageUrl(string $path, string $baseUrl = ''): string
    {
        if ($baseUrl !== '') {
            return rtrim($baseUrl, '/').'/'.ltrim($path, '/');
        }

        return Storage::disk((string) config('gateway.upload.disk', 'public'))->url($path);
    }

    protected function verifyFaceUpload(GatewaySdkClient $client, string $employeeId): array
    {
        $attempts = max(1, $this->settings->faceVerifyRetries());
        $delayMilliseconds = max(0, $this->settings->faceVerifyDelayMs());
        $lastResponse = [];

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            $lastResponse = $client->findFace($employeeId);

            if ($client->faceExists($lastResponse)) {
                return $lastResponse;
            }

            if ($attempt < $attempts && $delayMilliseconds > 0) {
                usleep($delayMilliseconds * 1000);
            }
        }

        return $lastResponse;
    }
}
