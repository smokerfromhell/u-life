<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Talent extends Model
{
    use HasFactory;

    protected $table = 'talents';

    protected $fillable = [
        'name',
        'description',
        'effect',
        'hidden',
        'image',
    ];

    /**
     * Get the characters that have this talent.
     */
    public function characters(): BelongsToMany
    {
        return $this->belongsToMany(Character::class, 'character_talent');
    }
}
