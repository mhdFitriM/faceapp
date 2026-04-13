<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\Enrollment;
use App\Models\ManagedUser;
use App\Models\ManagedUserDeviceSync;
use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DeviceMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_heartbeat_callbacks(): void
    {
        $response = $this->post('/api/device/callbacks/heartbeat', [
            'deviceKey' => 'DEVICE1234567890',
            'time' => '1712659200000',
            'ip' => '10.1.1.5',
            'personCount' => '12',
            'faceCount' => '9',
            'version' => '2.0.0',
            'freeDiskSpace' => '1024',
        ]);

        $response
            ->assertOk()
            ->assertSeeText('ok');

        $device = Device::query()->where('device_key', 'DEVICE1234567890')->firstOrFail();

        $this->assertSame('10.1.1.5', $device->last_ip);
        $this->assertSame(12, $device->person_count);
        $this->assertSame(9, $device->face_count);
        $this->assertSame('2.0.0', $device->last_version);

        $this->assertDatabaseHas('device_events', [
            'device_key' => 'DEVICE1234567890',
            'event_type' => 'heartbeat',
        ]);
    }

    public function test_dashboard_displays_devices_and_recent_enrollments(): void
    {
        Device::create([
            'device_key' => 'DEVICE1234567890',
            'last_ip' => '10.1.1.5',
            'last_seen_at' => now(),
        ]);

        Enrollment::create([
            'public_id' => 'ENR-TEST0001',
            'employee_id' => 'EMP-001',
            'name' => 'Nadia Tan',
            'status' => 'failed',
            'device_key' => 'DEVICE1234567890',
            'error_message' => 'Gateway did not confirm the uploaded face.',
        ]);

        config()->set('gateway.device_key', 'DEVICE1234567890');
        config()->set('gateway.secret', 'secret123');
        config()->set('gateway.base_url', 'http://gateway.local/api');

        Http::fake([
            'http://gateway.local/api/device/get' => Http::response([
                'code' => '200',
                'msg' => 'ok',
                'data' => [
                    'deviceKey' => 'DEVICE1234567890',
                    'online' => true,
                ],
            ]),
        ]);

        $response = $this->get('/devices');

        $response
            ->assertOk()
            ->assertSeeText('Callback Monitor')
            ->assertSeeText('DEVICE1234567890')
            ->assertSeeText('Gateway Reachable')
            ->assertSeeText('Nadia Tan')
            ->assertSeeText('Gateway did not confirm the uploaded face.');
    }

    public function test_it_pushes_callback_urls_to_the_gateway(): void
    {
        config()->set('app.url', 'https://api.example.com');
        config()->set('gateway.device_key', 'DEVICE1234567890');
        config()->set('gateway.secret', 'secret123');
        config()->set('gateway.base_url', 'http://gateway.local/api');
        config()->set('gateway.monitoring.callback_base_url', 'http://api.example.com');
        config()->set('gateway.monitoring.heartbeat_interval_seconds', 75);

        $expectedRecordUrl = 'http://api.example.com/api/device/callbacks/records';
        $expectedHeartbeatUrl = 'http://api.example.com/api/device/callbacks/heartbeat';
        $expectedPersonRegistrationUrl = 'http://api.example.com/api/device/callbacks/person-registrations';

        Http::fake([
            'http://gateway.local/api/device/setSevConfig' => Http::response([
                'code' => '200',
                'msg' => 'ok',
                'data' => true,
            ]),
        ]);

        $response = $this->post('/devices/configure-callbacks');

        $response
            ->assertRedirect('/devices')
            ->assertSessionHas('status');

        Http::assertSent(function (ClientRequest $request) use ($expectedHeartbeatUrl, $expectedPersonRegistrationUrl, $expectedRecordUrl): bool {
            return $request->url() === 'http://gateway.local/api/device/setSevConfig'
                && $request['deviceKey'] === 'DEVICE1234567890'
                && $request['secret'] === 'secret123'
                && $request['sevUploadRecRecordUrl'] === $expectedRecordUrl
                && $request['sevUploadDevHeartbeatUrl'] === $expectedHeartbeatUrl
                && $request['sevUploadRegPersonUrl'] === $expectedPersonRegistrationUrl
                && (string) $request['sevUploadDevHeartbeatInterval'] === '75';
        });
    }

    public function test_it_falls_back_to_gateway_secret_when_a_managed_device_secret_cannot_be_decrypted(): void
    {
        config()->set('gateway.device_key', 'DEVICE1234567890');
        config()->set('gateway.secret', 'env-secret-123');
        config()->set('gateway.base_url', 'http://gateway.local/api');
        config()->set('gateway.monitoring.callback_base_url', 'http://api.example.com');
        config()->set('gateway.monitoring.heartbeat_interval_seconds', 75);

        $legacyEncrypter = new Encrypter(random_bytes(32), config('app.cipher'));

        DB::table('devices')->insert([
            'device_key' => 'DEVICE1234567890',
            'name' => 'Main Gate',
            'secret' => $legacyEncrypter->encryptString('legacy-secret-123'),
            'is_managed' => true,
            'is_active' => true,
            'display_order' => 0,
            'person_type_default' => 1,
            'verify_style_default' => 1,
            'ac_group_number_default' => 0,
            'photo_quality_default' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Http::fake([
            'http://gateway.local/api/device/setSevConfig' => Http::response([
                'code' => '200',
                'msg' => 'ok',
                'data' => true,
            ]),
        ]);

        $response = $this->post('/devices/configure-callbacks');

        $response
            ->assertRedirect('/devices?device_id=1')
            ->assertSessionHas('status');

        Http::assertSent(function (ClientRequest $request): bool {
            return $request->url() === 'http://gateway.local/api/device/setSevConfig'
                && $request['deviceKey'] === 'DEVICE1234567890'
                && $request['secret'] === 'env-secret-123';
        });
    }

    public function test_person_registration_callback_creates_or_updates_local_user_sync(): void
    {
        Device::query()->create([
            'device_key' => 'DEVICE1234567890',
            'name' => 'Main Gate',
            'secret' => 'secret123',
            'is_managed' => true,
            'is_active' => true,
            'display_order' => 0,
            'person_type_default' => 1,
            'verify_style_default' => 1,
            'ac_group_number_default' => 0,
            'photo_quality_default' => 1,
        ]);

        ManagedUser::query()->create([
            'employee_id' => 'EMP4829',
            'name' => 'Alexandra Chen',
            'role' => 'Engineer',
            'is_active' => true,
            'person_type' => 1,
            'verify_style' => 1,
            'ac_group_number' => 0,
        ]);

        $response = $this->post('/api/device/callbacks/person-registrations', [
            'deviceKey' => 'DEVICE1234567890',
            'personType' => 1,
            'personSn' => 'EMP4829',
            'name' => 'Alexandra Device Name',
            'mobile' => '12345',
            'cardNo' => 'CARD100',
            'verifyStyle' => 20,
            'acGroupNumber' => 3,
            'time' => '1712659200000',
        ]);

        $response
            ->assertOk()
            ->assertSeeText('ok');

        $this->assertDatabaseHas('managed_users', [
            'employee_id' => 'EMP4829',
            'name' => 'Alexandra Chen',
            'role' => 'Engineer',
            'mobile' => '12345',
            'card_no' => 'CARD100',
            'verify_style' => 20,
            'ac_group_number' => 3,
        ]);

        $this->assertSame(1, ManagedUserDeviceSync::query()->count());

        $this->assertDatabaseHas('device_events', [
            'device_key' => 'DEVICE1234567890',
            'event_type' => 'person_registration',
            'person_sn' => 'EMP4829',
        ]);
    }
}
