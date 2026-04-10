<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\ManagedUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagedUserAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_device_tab_shows_users_even_when_not_synced_yet(): void
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

        ManagedUser::query()->create([
            'employee_id' => 'EMP4829',
            'name' => 'Alexandra Chen',
            'is_active' => true,
            'person_type' => 1,
            'verify_style' => 1,
            'ac_group_number' => 0,
        ]);

        $response = $this->get(route('admin.users.index', ['device_id' => $device->id]));

        $response
            ->assertOk()
            ->assertSeeText('Alexandra Chen')
            ->assertSeeText('EMP4829')
            ->assertSeeText('Not synced')
            ->assertSeeText('Not yet pushed to this device.');
    }
}
