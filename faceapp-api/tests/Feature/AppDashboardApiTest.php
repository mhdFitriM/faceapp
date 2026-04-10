<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\DeviceEvent;
use App\Models\Enrollment;
use App\Models\ManagedUser;
use App\Models\ManagedUserDeviceSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppDashboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_real_users_devices_and_activity(): void
    {
        $device = Device::query()->create([
            'device_key' => 'DEVICE1234567890',
            'secret' => 'secret123',
            'name' => 'HQ Lobby',
            'is_managed' => true,
            'is_active' => true,
            'last_seen_at' => now(),
            'person_type_default' => 1,
            'verify_style_default' => 1,
            'ac_group_number_default' => 0,
            'photo_quality_default' => 1,
        ]);

        $user = ManagedUser::query()->create([
            'employee_id' => 'EMP4829',
            'name' => 'Alexandra Chen',
            'role' => 'Senior Engineer',
            'department' => 'Platform Infrastructure',
            'access_level' => 'Level 3',
            'is_active' => true,
            'photo_public_url' => 'https://face-api.example.com/storage/face-uploads/alex.jpg',
            'last_enrolled_at' => now(),
        ]);

        ManagedUserDeviceSync::query()->create([
            'managed_user_id' => $user->id,
            'device_id' => $device->id,
            'sync_status' => 'synced',
            'face_status' => 'verified',
            'last_synced_at' => now(),
            'last_face_synced_at' => now(),
        ]);

        Enrollment::query()->create([
            'managed_user_id' => $user->id,
            'employee_id' => $user->employee_id,
            'name' => $user->name,
            'status' => 'verified',
            'device_key' => $device->device_key,
            'enrolled_at' => now(),
        ]);

        DeviceEvent::query()->create([
            'device_key' => $device->device_key,
            'event_type' => 'access_record',
            'person_sn' => $user->employee_id,
            'result_flag' => 1,
            'event_time' => now(),
            'payload' => ['deviceKey' => $device->device_key],
        ]);

        $response = $this->getJson('/api/app/dashboard?managed_user_id='.$user->id);

        $response
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('active_devices.0.device_key', 'DEVICE1234567890')
            ->assertJsonPath('users.0.employee_id', 'EMP4829')
            ->assertJsonPath('selected_user.employee_id', 'EMP4829')
            ->assertJsonPath('selected_user.device_syncs.0.device_key', 'DEVICE1234567890')
            ->assertJsonPath('selected_user.device_syncs.0.face_status', 'verified');

        $this->assertNotEmpty($response->json('selected_user.activity'));
    }

    public function test_it_keeps_users_inactive_until_a_face_is_actually_enrolled(): void
    {
        $device = Device::query()->create([
            'device_key' => 'DEVICE1234567890',
            'secret' => 'secret123',
            'name' => 'HQ Lobby',
            'is_managed' => true,
            'is_active' => true,
            'last_seen_at' => now(),
            'person_type_default' => 1,
            'verify_style_default' => 1,
            'ac_group_number_default' => 0,
            'photo_quality_default' => 1,
        ]);

        $user = ManagedUser::query()->create([
            'employee_id' => 'EMP4829',
            'name' => 'Alexandra Chen',
            'role' => 'Senior Engineer',
            'department' => 'Platform Infrastructure',
            'access_level' => 'Level 3',
            'is_active' => true,
        ]);

        ManagedUserDeviceSync::query()->create([
            'managed_user_id' => $user->id,
            'device_id' => $device->id,
            'sync_status' => 'synced',
            'face_status' => null,
            'last_synced_at' => now(),
        ]);

        $response = $this->getJson('/api/app/dashboard?managed_user_id='.$user->id);

        $response
            ->assertOk()
            ->assertJsonPath('users.0.status', 'inactive')
            ->assertJsonPath('users.0.recognition_id', null)
            ->assertJsonPath('selected_user.status', 'inactive')
            ->assertJsonPath('selected_user.recognition_id', null);
    }
}
