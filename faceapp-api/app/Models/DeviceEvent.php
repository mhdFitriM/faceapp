<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceEvent extends Model
{
    protected $fillable = [
        'device_key',
        'event_type',
        'event_uid',
        'person_sn',
        'result_flag',
        'event_time',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'result_flag' => 'integer',
            'event_time' => 'datetime',
            'payload' => 'array',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_key', 'device_key');
    }
}
