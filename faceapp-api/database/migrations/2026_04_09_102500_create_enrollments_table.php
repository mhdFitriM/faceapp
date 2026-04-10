<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id')->unique();
            $table->string('employee_id')->index();
            $table->string('name');
            $table->string('status')->default('pending');
            $table->string('device_key')->nullable();
            $table->string('photo_path')->nullable();
            $table->text('photo_public_url')->nullable();
            $table->string('gateway_person_status')->nullable();
            $table->string('gateway_face_status')->nullable();
            $table->json('gateway_person_response')->nullable();
            $table->json('gateway_face_response')->nullable();
            $table->json('verification_response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
