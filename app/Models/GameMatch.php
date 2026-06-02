<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'team_id',
    'opponent_name',
    'opponent_logo',
    'scheduled_at',
    'match_url',
    'team_score',
    'opponent_score',
    'status',
    'details'
])]
class GameMatch extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'details' => 'array',
        ];
    }

    /**
     * Ein Match gehört zu einem eurer Teams.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
