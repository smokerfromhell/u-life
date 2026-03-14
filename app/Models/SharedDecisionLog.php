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
        'user_name',
        'day',
        'event_type',
        'event_id',
        'choice_index',
        'event_title',
        'choice_text',
        'effects',
        'mbti',
        'data',
    ];

    protected $casts = [
        'is_guest' => 'boolean',
        'data' => 'array',
    ];
    
    /**
     * Get the MBTI type - checks dedicated field first, then falls back to data field
     */
    public function getMbtiTypeAttribute(): string
    {
        // Return from dedicated field if available
        if (!empty($this->attributes['mbti'])) {
            return $this->attributes['mbti'];
        }
        
        // Fallback to data field (array cast handles JSON automatically)
        try {
            $data = $this->data;
            if (is_array($data) && isset($data['mbti']) && !empty($data['mbti'])) {
                return $data['mbti'];
            }
        } catch (\Exception $e) {
            // If retrieval fails, return Unknown
        }
        
        return 'Analyzing...';
    }
}
