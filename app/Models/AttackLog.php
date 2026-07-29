<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttackLog extends Model
{
    protected $fillable = [

        'ip_address',

        'route',

        'payload',

        'pattern',

        'method'

    ];
}