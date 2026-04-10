<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('managed_user_device_syncs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('managed_user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('sync_status')->default('pending');
            $table->string('face_status')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('last_face_synced_at')->nullable();
            $table->text('last_error_message')->nullable();
            $table->json('gateway_person_response')->nullable();
            $table->json('gateway_face_response')->nullable();
            $table->json('verification_response')->nullable();
            $table->timestamps();

            $table->unique(['managed_user_id', 'device_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('managed_user_device_syncs');
    }
};
