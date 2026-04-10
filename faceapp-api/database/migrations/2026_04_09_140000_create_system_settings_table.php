<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('gateway_base_url')->nullable();
            $table->string('public_storage_base_url')->nullable();
            $table->string('gateway_image_base_url')->nullable();
            $table->string('gateway_callback_base_url')->nullable();
            $table->unsignedInteger('heartbeat_interval_seconds')->default(60);
            $table->unsignedInteger('online_window_seconds')->default(180);
            $table->unsignedInteger('person_verify_retries')->default(5);
            $table->unsignedInteger('person_verify_delay_ms')->default(1000);
            $table->unsignedInteger('face_verify_retries')->default(5);
            $table->unsignedInteger('face_verify_delay_ms')->default(1500);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
