<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table): void {
            $table->string('name')->nullable()->after('device_key');
            $table->string('client_name')->nullable()->after('name');
            $table->string('branch_name')->nullable()->after('client_name');
            $table->text('secret')->nullable()->after('branch_name');
            $table->boolean('is_managed')->default(false)->after('secret');
            $table->boolean('is_active')->default(false)->after('is_managed');
            $table->unsignedInteger('display_order')->default(0)->after('is_active');
            $table->unsignedTinyInteger('person_type_default')->default(1)->after('display_order');
            $table->unsignedInteger('verify_style_default')->default(1)->after('person_type_default');
            $table->unsignedInteger('ac_group_number_default')->default(0)->after('verify_style_default');
            $table->unsignedTinyInteger('photo_quality_default')->default(1)->after('ac_group_number_default');
            $table->text('notes')->nullable()->after('photo_quality_default');
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table): void {
            $table->dropColumn([
                'name',
                'client_name',
                'branch_name',
                'secret',
                'is_managed',
                'is_active',
                'display_order',
                'person_type_default',
                'verify_style_default',
                'ac_group_number_default',
                'photo_quality_default',
                'notes',
            ]);
        });
    }
};
