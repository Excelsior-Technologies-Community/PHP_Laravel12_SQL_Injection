<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuarantinedIp extends Model
{
    protected $fillable = [
        'ip_address',
        'reason',
        'last_payload',
        'violation_count',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
