<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table): void {
            $table->id();
            $table->string('device_key')->unique();
            $table->string('last_ip')->nullable();
            $table->string('last_version')->nullable();
            $table->unsignedInteger('person_count')->nullable();
            $table->unsignedInteger('face_count')->nullable();
            $table->string('free_disk_space')->nullable();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamp('last_record_at')->nullable()->index();
            $table->json('last_heartbeat_payload')->nullable();
            $table->json('last_record_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
