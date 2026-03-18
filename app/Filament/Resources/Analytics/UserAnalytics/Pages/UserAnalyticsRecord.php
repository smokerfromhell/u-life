<?php

namespace App\Filament\Resources\Analytics\UserAnalytics\Pages;

use Illuminate\Database\Eloquent\Model;

class UserAnalyticsRecord extends Model
{
    protected $table = 'shared_decision_logs';
    
    protected $fillable = [
        'user_name',
        'user_id',
        'is_guest',
        'decision_count',
        'last_active',
        'first_active',
    ];

    protected $casts = [
        'last_active' => 'datetime',
        'first_active' => 'datetime',
    ];

    public $incrementing = false;
    public $timestamps = false;
    
    public static function boot()
    {
        parent::boot();

        static::forceDeleted(function ($record) {
            //
        });
    }
}

