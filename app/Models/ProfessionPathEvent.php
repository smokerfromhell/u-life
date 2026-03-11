<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfessionPathEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'profession',
        'event_choice',
        'title',
        'description',
        'outcome',
        'stat_effects',
        'choices',
        'image',
        'weight',
    ];

    /**
     * Get the stat effects as an array
     */
    public function getStatEffectsArray(): array
    {
        if (is_array($this->stat_effects)) {
            return $this->stat_effects;
        }
        
        $effects = [];
        if (!empty($this->stat_effects)) {
            $parts = explode(',', $this->stat_effects);
            foreach ($parts as $part) {
                $part = trim($part);
                if (preg_match('/([+-]\d+)\s+(\w+)/', $part, $matches)) {
                    $effects[$matches[2]] = (int)$matches[1];
                }
            }
        }
        return $effects;
    }
}
