<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'game', 'logo', 'description', 'leader_id', 'coach_id', 'faceit_id', 'league_links'])]
class Team extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'league_links' => 'array', // Verwandelt das JSON automatisch in ein PHP-Array
        ];
    }

    /**
     * Ein Team hat viele Member (User).
     */
    public function members(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Ein Team hat einen Team Leader (User).
     */
    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    /**
     * Ein Team hat (optional) einen Coach (User).
     */
    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    /**
     * Ein Team hat viele Matches.
     */
    public function gameMatches(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GameMatch::class, 'team_id');
    }
}