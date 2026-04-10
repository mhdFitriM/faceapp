<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Enrollment extends Model
{
    protected $fillable = [
        'public_id',
        'managed_user_id',
        'employee_id',
        'name',
        'status',
        'device_key',
        'photo_path',
        'photo_public_url',
        'gateway_person_status',
        'gateway_face_status',
        'gateway_person_response',
        'gateway_face_response',
        'verification_response',
        'sync_results',
        'error_message',
        'enrolled_at',
    ];

    protected function casts(): array
    {
        return [
            'gateway_person_response' => 'array',
            'gateway_face_response' => 'array',
            'verification_response' => 'array',
            'sync_results' => 'array',
            'enrolled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $enrollment): void {
            if (! $enrollment->public_id) {
                $enrollment->public_id = 'ENR-'.Str::upper(Str::random(10));
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function managedUser(): BelongsTo
    {
        return $this->belongsTo(ManagedUser::class);
    }
}
