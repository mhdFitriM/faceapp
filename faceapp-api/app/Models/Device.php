<?php

namespace App\Models;

use App\Services\SystemSettingsService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Device extends Model
{
    protected $fillable = [
        'device_key',
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
        'last_ip',
        'last_version',
        'person_count',
        'face_count',
        'free_disk_space',
        'last_seen_at',
        'last_record_at',
        'last_heartbeat_payload',
        'last_record_payload',
    ];

    protected $appends = [
        'is_online',
    ];

    protected function casts(): array
    {
        return [
            'person_count' => 'integer',
            'face_count' => 'integer',
            'secret' => 'encrypted',
            'is_managed' => 'boolean',
            'is_active' => 'boolean',
            'display_order' => 'integer',
            'person_type_default' => 'integer',
            'verify_style_default' => 'integer',
            'ac_group_number_default' => 'integer',
            'photo_quality_default' => 'integer',
            'last_seen_at' => 'datetime',
            'last_record_at' => 'datetime',
            'last_heartbeat_payload' => 'array',
            'last_record_payload' => 'array',
        ];
    }

    public function events(): HasMany
    {
        return $this->hasMany(DeviceEvent::class, 'device_key', 'device_key');
    }

    public function userSyncs(): HasMany
    {
        return $this->hasMany(ManagedUserDeviceSync::class);
    }

    public function managedUsers(): HasManyThrough
    {
        return $this->hasManyThrough(
            ManagedUser::class,
            ManagedUserDeviceSync::class,
            'device_id',
            'id',
            'id',
            'managed_user_id',
        );
    }

    public function getIsOnlineAttribute(): bool
    {
        if (! $this->last_seen_at) {
            return false;
        }

        $window = (int) app(SystemSettingsService::class)->onlineWindowSeconds();

        return $this->last_seen_at->greaterThanOrEqualTo(now()->subSeconds($window));
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->name) {
            return $this->name;
        }

        $parts = array_filter([$this->client_name, $this->branch_name]);

        return $parts !== [] ? implode(' / ', $parts) : $this->device_key;
    }
}
