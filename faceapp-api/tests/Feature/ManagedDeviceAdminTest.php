<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\ManagedUser;
use App\Models\ManagedUserDeviceSync;
use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ManagedDeviceAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_updating_a_device_does_not_delete_it(): void
    {
        $device = Device::query()->create([
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

        $response = $this->put(route('admin.devices.update', $device), [
            'device_key' => 'DEVICE1234567890',
            'name' => 'Updated Main Gate',
            'client_name' => 'Client A',
            'branch_name' => 'Branch 1',
            'secret' => '',
            'is_active' => '1',
            'display_order' => 2,
            'person_type_default' => 1,
            'verify_style_default' => 1,
            'ac_group_number_default' => 0,
            'photo_quality_default' => 1,
            'notes' => 'Updated notes',
        ]);

        $response
            ->assertRedirect(route('admin.devices.edit', $device))
            ->assertSessionHas('status', 'Device updated.');

        $this->assertDatabaseCount('devices', 1);
        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'device_key' => 'DEVICE1234567890',
            'name' => 'Updated Main Gate',
            'client_name' => 'Client A',
            'branch_name' => 'Branch 1',
            'display_order' => 2,
            'is_managed' => true,
            'is_active' => true,
        ]);
    }

    public function test_it_imports_users_from_a_device_into_local_records(): void
    {
        config()->set('gateway.base_url', 'http://gateway.local/api');

        $device = Device::query()->create([
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

        Http::fake([
            'http://gateway.local/api/person/findList' => Http::response([
                'code' => '200',
                'msg' => 'ok',
                'data' => [
                    'list' => [
                        [
                            'sn' => 'EMP4829',
                            'name' => 'Alexandra Device Name',
                            'personType' => 1,
                            'verifyStyle' => 1,
                            'acGroupNumber' => 0,
                            'mobile' => '12345',
                        ],
                        [
                            'sn' => 'EMP9001',
                            'name' => 'New Device User',
                            'personType' => 2,
                            'verifyStyle' => 20,
                            'acGroupNumber' => 3,
                            'cardNo' => 'CARD100',
                        ],
                    ],
                    'total' => 2,
                ],
            ]),
        ]);

        $response = $this->post(route('admin.devices.import-users', $device));

        $response
            ->assertRedirect(route('admin.users.index', ['device_id' => $device->id]))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('managed_users', [
            'employee_id' => 'EMP4829',
            'name' => 'Alexandra Chen',
            'role' => 'Engineer',
        ]);

        $this->assertDatabaseHas('managed_users', [
            'employee_id' => 'EMP9001',
            'name' => 'New Device User',
            'person_type' => 2,
            'verify_style' => 20,
            'ac_group_number' => 3,
            'card_no' => 'CARD100',
        ]);

        $this->assertSame(2, ManagedUserDeviceSync::query()->count());

        Http::assertSent(function (ClientRequest $request): bool {
            return $request->url() === 'http://gateway.local/api/person/findList'
                && $request['deviceKey'] === 'DEVICE1234567890'
                && $request['secret'] === 'secret123';
        });
    }

    public function test_it_shows_a_helpful_error_when_a_device_secret_cannot_be_decrypted_and_no_fallback_exists(): void
    {
        config()->set('gateway.base_url', 'http://gateway.local/api');
        config()->set('gateway.secret', null);

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

        $device = Device::query()->where('device_key', 'DEVICE1234567890')->firstOrFail();

        $response = $this->from(route('admin.devices.index'))
            ->post(route('admin.devices.configure-callbacks', $device));

        $response
            ->assertRedirect(route('admin.devices.index'))
            ->assertSessionHas('error', 'Callback configuration failed: Stored device secret could not be decrypted with the current APP_KEY. Re-save the device secret in admin and try again.');
    }
}
