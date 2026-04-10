<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagedUserDeviceSync extends Model
{
    protected $fillable = [
        'managed_user_id',
        'device_id',
        'sync_status',
        'face_status',
        'last_synced_at',
        'last_face_synced_at',
        'last_error_message',
        'gateway_person_response',
        'gateway_face_response',
        'verification_response',
    ];

    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
            'last_face_synced_at' => 'datetime',
            'gateway_person_response' => 'array',
            'gateway_face_response' => 'array',
            'verification_response' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(ManagedUser::class, 'managed_user_id');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
