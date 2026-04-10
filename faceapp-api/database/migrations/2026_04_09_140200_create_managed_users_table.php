<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('managed_users', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id')->unique();
            $table->string('employee_id')->unique();
            $table->string('name');
            $table->string('role')->nullable();
            $table->string('department')->nullable();
            $table->string('access_level')->nullable();
            $table->date('joined_on')->nullable();
            $table->string('mobile')->nullable();
            $table->string('card_no')->nullable();
            $table->string('id_card')->nullable();
            $table->string('voucher_code')->nullable();
            $table->string('verify_pwd')->nullable();
            $table->unsignedTinyInteger('person_type')->default(1);
            $table->unsignedInteger('verify_style')->default(1);
            $table->unsignedInteger('ac_group_number')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('photo_path')->nullable();
            $table->text('photo_public_url')->nullable();
            $table->timestamp('last_enrolled_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('managed_users');
    }
};
