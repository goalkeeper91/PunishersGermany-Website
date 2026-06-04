<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

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
     * Ein Match gehört zu einem der Teams.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Aktualisiert das Match mit neuen API-Daten und prüft auf Terminänderungen.
     */
    public function updateFromApi(array $apiData): void
    {
        // Wir merken uns den alten Termin vor der Änderung
        $oldSchedule = $this->scheduled_at;

        // Daten zuweisen und speichern
        $this->update([
            'opponent_name' => $apiData['opponent']['name'],
            'scheduled_at' => Carbon::parse($apiData['started_at']),
            'status' => $this->mapFaceitStatus($apiData['status']),
        ]);

        // Wenn sich das Datum geändert hat und es kein neues Match war
        if ($oldSchedule && $this->wasChanged('scheduled_at')) {
            // Hier Event feuern oder Logik triggern
            Log::info("Match {$this->id} wurde von {$oldSchedule} auf {$this->scheduled_at} verschoben.");
        }
    }

    protected function mapFaceitStatus(string $status): string
    {
        return match ($status) {
            'FINISHED' => 'finished',
            'LIVE' => 'live',
            default => 'scheduled',
        };
    }
}
