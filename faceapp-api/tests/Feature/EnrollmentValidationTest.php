<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_rejects_employee_ids_with_non_alphanumeric_characters(): void
    {
        $response = $this->postJson('/api/enrollments', [
            'employee_id' => 'EMP-4829',
            'name' => 'Alexandra Chen',
            'photo_data_url' => 'data:image/jpeg;base64,'.base64_encode('fake-image'),
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['employee_id']);
    }
}
