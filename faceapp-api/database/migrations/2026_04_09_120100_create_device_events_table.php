<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_events', function (Blueprint $table): void {
            $table->id();
            $table->string('device_key')->index();
            $table->string('event_type')->index();
            $table->string('event_uid')->nullable()->index();
            $table->string('person_sn')->nullable()->index();
            $table->unsignedTinyInteger('result_flag')->nullable();
            $table->timestamp('event_time')->nullable()->index();
            $table->json('payload');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_events');
    }
};
