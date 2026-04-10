<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceEvent;
use App\Models\Enrollment;
use App\Models\ManagedUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppDashboardController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $devices = Device::query()
            ->where('is_managed', true)
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        $users = ManagedUser::query()
            ->where('is_active', true)
            ->with(['syncs.device'])
            ->orderBy('name')
            ->get();

        $selectedUser = $users->firstWhere('id', $request->integer('managed_user_id'))
            ?? $users->first();

        return response()->json([
            'ok' => true,
            'active_devices' => $devices->map(fn (Device $device): array => [
                'id' => $device->id,
                'device_key' => $device->device_key,
                'name' => $device->display_name,
                'is_online' => $device->is_online,
                'person_count' => $device->person_count,
                'face_count' => $device->face_count,
            ])->all(),
            'users' => $users->map(fn (ManagedUser $user): array => $this->summary($user))->all(),
            'selected_user' => $selectedUser ? $this->detail($selectedUser) : null,
        ]);
    }

    protected function summary(ManagedUser $user): array
    {
        $latestEnrollment = $user->enrollments()->latest()->first();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'employee_id' => $user->employee_id,
            'role' => $user->role,
            'department' => $user->department,
            'status' => $this->resolveStatus($user, $latestEnrollment),
            'face_photo' => $user->photo_public_url,
            'recognition_id' => $this->resolveRecognitionId($user, $latestEnrollment),
        ];
    }

    protected function detail(ManagedUser $user): array
    {
        $latestEnrollment = $user->enrollments()->latest()->first();
        $events = $this->activity($user);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->role ?: 'No role set',
            'department' => $user->department ?: 'No department set',
            'employee_id' => $user->employee_id,
            'joined' => $user->joined_on?->format('M Y') ?: 'Not set',
            'access_level' => $user->access_level ?: 'Not set',
            'status' => $this->resolveStatus($user, $latestEnrollment),
            'face_photo' => $user->photo_public_url,
            'recognition_id' => $this->resolveRecognitionId($user, $latestEnrollment),
            'enrolled_at' => $user->last_enrolled_at?->toIso8601String(),
            'activity' => $events,
            'device_syncs' => $user->syncs
                ->sortBy(fn ($sync) => $sync->device?->display_order ?? PHP_INT_MAX)
                ->map(fn ($sync): array => [
                    'device_id' => $sync->device_id,
                    'device_name' => $sync->device?->display_name ?? 'Unknown device',
                    'device_key' => $sync->device?->device_key,
                    'is_online' => $sync->device?->is_online ?? false,
                    'sync_status' => $sync->sync_status,
                    'face_status' => $sync->face_status,
                    'last_synced_at' => $sync->last_synced_at?->toIso8601String(),
                    'last_face_synced_at' => $sync->last_face_synced_at?->toIso8601String(),
                    'last_error_message' => $sync->last_error_message,
                ])
                ->values()
                ->all(),
        ];
    }

    protected function activity(ManagedUser $user): array
    {
        $enrollmentEvents = $user->enrollments()
            ->latest()
            ->limit(6)
            ->get()
            ->map(function (Enrollment $enrollment): array {
                $type = $enrollment->status === 'verified' ? 'success' : ($enrollment->status === 'partial' ? 'warning' : 'error');

                return [
                    'label' => match ($enrollment->status) {
                        'verified' => 'Face enrolled across active devices',
                        'partial' => 'Face enrollment completed with some device issues',
                        default => 'Face enrollment failed',
                    },
                    'time' => $enrollment->updated_at?->toIso8601String(),
                    'type' => $type,
                    'tag' => 'Enrollment',
                    'sort_time' => $enrollment->updated_at?->timestamp ?? 0,
                ];
            });

        $deviceEvents = DeviceEvent::query()
            ->where('person_sn', $user->employee_id)
            ->latest('event_time')
            ->limit(6)
            ->get()
            ->map(function (DeviceEvent $event): array {
                $resultFlag = (int) ($event->result_flag ?? 0);
                $type = $resultFlag === 1 ? 'success' : ($resultFlag === 2 ? 'warning' : 'info');

                return [
                    'label' => $event->event_type === 'person_registration'
                        ? 'Device reported local person registration'
                        : ($resultFlag === 1 ? 'Access granted on device' : 'Access event reported'),
                    'time' => $event->event_time?->toIso8601String() ?? $event->created_at?->toIso8601String(),
                    'type' => $type,
                    'tag' => $event->event_type === 'person_registration' ? 'Device' : 'Access',
                    'sort_time' => $event->event_time?->timestamp ?? $event->created_at?->timestamp ?? 0,
                ];
            });

        return $enrollmentEvents
            ->concat($deviceEvents)
            ->sortByDesc('sort_time')
            ->take(8)
            ->map(function (array $event): array {
                unset($event['sort_time']);

                return $event;
            })
            ->values()
            ->all();
    }

    protected function resolveStatus(ManagedUser $user, ?Enrollment $latestEnrollment): string
    {
        if ($this->hasVerifiedFace($user, $latestEnrollment)) {
            return 'active';
        }

        if ($latestEnrollment?->status === 'pending' || $user->syncs->contains(fn ($sync) => $sync->sync_status === 'pending')) {
            return 'pending';
        }

        return 'inactive';
    }

    protected function resolveRecognitionId(ManagedUser $user, ?Enrollment $latestEnrollment): ?string
    {
        if ($latestEnrollment && in_array($latestEnrollment->status, ['verified', 'partial'], true)) {
            return $latestEnrollment->public_id;
        }

        if ($user->syncs->contains(fn ($sync) => $sync->face_status === 'verified')) {
            return $user->employee_id;
        }

        return null;
    }

    protected function hasVerifiedFace(ManagedUser $user, ?Enrollment $latestEnrollment): bool
    {
        if ($latestEnrollment && in_array($latestEnrollment->status, ['verified', 'partial'], true)) {
            return true;
        }

        return $user->syncs->contains(fn ($sync) => $sync->face_status === 'verified');
    }
}
