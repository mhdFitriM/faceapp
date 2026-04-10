<?php

namespace Tests\Feature;

use App\Models\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EnrollmentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_enrolls_a_face_and_marks_it_verified(): void
    {
        Storage::fake('public');

        config()->set('gateway.device_key', 'DEVICE1234567890');
        config()->set('gateway.secret', 'secret123');
        config()->set('gateway.base_url', 'http://gateway.local/api');
        config()->set('gateway.upload.public_base_url', 'http://127.0.0.1:8000/storage');

        Device::query()->create([
            'device_key' => 'DEVICE1234567890',
            'secret' => 'secret123',
            'name' => 'Main Gate',
            'is_managed' => true,
            'is_active' => true,
            'person_type_default' => 1,
            'verify_style_default' => 1,
            'ac_group_number_default' => 0,
            'photo_quality_default' => 1,
        ]);

        Http::fake([
            'http://gateway.local/api/person/find' => Http::sequence()
                ->push([
                    'code' => '200',
                    'msg' => 'ok',
                    'data' => null,
                ])
                ->push([
                    'code' => '200',
                    'msg' => 'ok',
                    'data' => [
                        'sn' => 'EMP4829',
                        'name' => 'Alexandra Chen',
                    ],
                ]),
            'http://gateway.local/api/person/merge' => Http::response([
                'code' => '000',
                'msg' => 'Request is successful',
                'success' => true,
                'data' => true,
            ]),
            'http://gateway.local/api/face/merge' => Http::response([
                'code' => '000',
                'msg' => 'Request is successful',
                'success' => true,
                'data' => true,
            ]),
            'http://gateway.local/api/face/find' => Http::response([
                'code' => '200',
                'msg' => 'ok',
                'data' => [
                    'personSn' => 'EMP4829',
                    'imgBase64' => base64_encode('verified'),
                ],
            ]),
        ]);

        $response = $this->postJson('/api/enrollments', [
            'employee_id' => 'EMP4829',
            'name' => 'Alexandra Chen',
            'photo_data_url' => 'data:image/jpeg;base64,'.base64_encode('fake-image'),
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('enrollment.status', 'verified')
            ->assertJsonCount(1, 'enrollment.sync_results');

        Http::assertSentCount(5);
    }

    public function test_it_returns_partial_success_when_at_least_one_device_verifies(): void
    {
        Storage::fake('public');

        config()->set('gateway.base_url', 'http://gateway.local/api');
        config()->set('gateway.upload.public_base_url', 'http://127.0.0.1:8000/storage');
        config()->set('gateway.verification.person_retries', 1);
        config()->set('gateway.verification.retries', 1);

        Device::query()->create([
            'device_key' => 'DEVICE1234567890',
            'secret' => 'secret123',
            'name' => 'Main Gate',
            'is_managed' => true,
            'is_active' => true,
            'display_order' => 1,
            'person_type_default' => 1,
            'verify_style_default' => 1,
            'ac_group_number_default' => 0,
            'photo_quality_default' => 1,
        ]);

        Device::query()->create([
            'device_key' => 'DEVICE1234567891',
            'secret' => 'secret123',
            'name' => 'Side Gate',
            'is_managed' => true,
            'is_active' => true,
            'display_order' => 2,
            'person_type_default' => 1,
            'verify_style_default' => 1,
            'ac_group_number_default' => 0,
            'photo_quality_default' => 1,
        ]);

        Http::fake([
            'http://gateway.local/api/person/find' => Http::sequence()
                ->push([
                    'code' => '200',
                    'msg' => 'ok',
                    'data' => [
                        'sn' => 'EMP4829',
                        'name' => 'Alexandra Chen',
                    ],
                ])
                ->push([
                    'code' => '200',
                    'msg' => 'ok',
                    'data' => [
                        'sn' => 'EMP4829',
                        'name' => 'Alexandra Chen',
                    ],
                ]),
            'http://gateway.local/api/person/merge' => Http::sequence()
                ->push([
                    'code' => '000',
                    'msg' => 'Request is successful',
                    'success' => true,
                    'data' => true,
                ])
                ->push([
                    'code' => '000',
                    'msg' => 'Request is successful',
                    'success' => true,
                    'data' => true,
                ]),
            'http://gateway.local/api/face/merge' => Http::sequence()
                ->push([
                    'code' => '000',
                    'msg' => 'Request is successful',
                    'success' => true,
                    'data' => true,
                ])
                ->push([
                    'code' => '000',
                    'msg' => 'Request is successful',
                    'success' => true,
                    'data' => true,
                ]),
            'http://gateway.local/api/face/find' => Http::sequence()
                ->push([
                    'code' => '200',
                    'msg' => 'ok',
                    'data' => [
                        'personSn' => 'EMP4829',
                        'imgBase64' => base64_encode('verified'),
                    ],
                ])
                ->push([
                    'code' => '200',
                    'msg' => 'ok',
                    'data' => null,
                ]),
        ]);

        $response = $this->postJson('/api/enrollments', [
            'employee_id' => 'EMP4829',
            'name' => 'Alexandra Chen',
            'photo_data_url' => 'data:image/jpeg;base64,'.base64_encode('fake-image'),
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('enrollment.status', 'partial');

        $this->assertSame(
            1,
            collect($response->json('enrollment.sync_results'))
                ->where('status', 'verified')
                ->count(),
        );
    }
}
