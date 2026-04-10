<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ManagedUser extends Model
{
    protected $fillable = [
        'public_id',
        'employee_id',
        'name',
        'role',
        'department',
        'access_level',
        'joined_on',
        'mobile',
        'card_no',
        'id_card',
        'voucher_code',
        'verify_pwd',
        'person_type',
        'verify_style',
        'ac_group_number',
        'is_active',
        'photo_path',
        'photo_public_url',
        'last_enrolled_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'joined_on' => 'date',
            'is_active' => 'boolean',
            'last_enrolled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $user): void {
            if (! $user->public_id) {
                $user->public_id = 'USR-'.Str::upper(Str::random(10));
            }
        });
    }

    public function syncs(): HasMany
    {
        return $this->hasMany(ManagedUserDeviceSync::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }
}
