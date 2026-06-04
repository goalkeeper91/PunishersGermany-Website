<?php

namespace App\Services;

use App\Models\Team;
use App\Models\GameMatch;
use Illuminate\Support\Facades\Http;

class FaceitService
{
    /**
     * Holt die Matches von Faceit und verarbeitet sie über das GameMatch-Model.
     */
    public function syncTeamMatches(Team $team): void
    {
        // Dummy-Struktur für den Moment, bis wir die echte API anbinden
        // Hier simulieren wir ein Match, das von der API kommen würde
        $mockApiMatches = [
            [
                'match_id' => 'faceit-match-12345',
                'started_at' => now()->addDays(2)->toIso8601String(),
                'status' => 'scheduled',
                'opponent' => ['name' => 'Enemy Esports']
            ]
        ];

        foreach ($mockApiMatches as $apiMatch) {
            // Findet das Match anhand der Faceit-ID im JSON-Feld 'details' oder erstellt ein neues
            $match = GameMatch::firstOrNew([
                'team_id' => $team->id,
                'details->faceit_match_id' => $apiMatch['match_id']
            ]);

            // Übergibt die Rohdaten an das Model zur Eigenspeicherung
            $match->updateFromApi($apiMatch);
        }
    }
}
