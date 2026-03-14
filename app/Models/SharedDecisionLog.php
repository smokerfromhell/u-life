<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SharedDecisionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'anon_user_id',
        'anon_character_id',
        'is_guest',
        'day',
        'event_type',
        'event_id',
        'choice_index',
        'data',
    ];

    protected $casts = [
        'is_guest' => 'boolean',
        'data' => 'encrypted:array',
    ];
}
