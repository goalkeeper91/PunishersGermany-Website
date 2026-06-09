<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\GameMatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class MatchController extends Controller
{
    /**
     * Gibt eine Liste aller Matches zurück (Öffentlich für Kalender/Historie)
     */
    public function index(): JsonResponse
    {
        // Wir laden die Matches sortiert nach Datum, inklusive dem dazugehörigen Team
        $matches = GameMatch::with('team')
            ->orderBy('scheduled_at', 'desc')
            ->get();

        return response()->json($matches);
    }

    /**
     * Erstellt ein Match MANUELL (Nur Admin/Orga für Custom-Matches)
     */
    public function store(Request $request): JsonResponse
    {
        if (!in_array($request->user()->role, [UserRole::ADMIN, UserRole::ORGA])) {
            abort(403, 'Du hast keine Berechtigung, Matches manuell anzulegen.');
        }

        $validated = $request->validate([
            'team_id' => 'required|exists:teams,id',
            'opponent_name' => 'required|string|max:255',
            'opponent_logo' => 'nullable|string',
            'scheduled_at' => 'required|date',
            'match_url' => 'nullable|url',
            'status' => 'required|string|in:scheduled,live,finished',
            'details' => 'nullable|array',
        ]);

        // Standardmäßig markieren wir ein händisches Match in den Details, 
        // damit es später nicht aus Versehen vom Faceit-Sync überschrieben wird.
        $validated['details'] = array_merge($request->input('details', []), [
            'source' => 'manual'
        ]);

        $match = GameMatch::create($validated);

        return response()->json([
            'message' => 'Match erfolgreich manuell angelegt!',
            'match' => $match
        ], 201);
    }

    /**
     * Zeigt die Details eines spezifischen Matches (Öffentlich)
     */
    public function show(GameMatch $match): JsonResponse
    {
        return response()->json($match->load('team'));
    }

    /**
     * Aktualisiert ein Match MANUELL (Nur Admin/Orga, z.B. für Ergebnis-Eintragung)
     */
    public function update(Request $request, GameMatch $match): JsonResponse
    {
        if (!in_array($request->user()->role, [UserRole::ADMIN, UserRole::ORGA])) {
            abort(403, 'Du hast keine Berechtigung, dieses Match zu bearbeiten.');
        }

        $validated = $request->validate([
            'opponent_name' => 'sometimes|required|string|max:255',
            'opponent_logo' => 'nullable|string',
            'scheduled_at' => 'sometimes|required|date',
            'match_url' => 'nullable|url',
            'team_score' => 'nullable|integer|min:0',
            'opponent_score' => 'nullable|integer|min:0',
            'status' => 'sometimes|required|string|in:scheduled,live,finished',
            'details' => 'nullable|array',
        ]);

        $match->update($validated);

        return response()->json([
            'message' => 'Match erfolgreich aktualisiert!',
            'match' => $match
        ]);
    }

    /**
     * Löscht ein Match manuell (Nur Admin/Orga)
     */
    public function destroy(Request $request, GameMatch $match): JsonResponse
    {
        if (!in_array($request->user()->role, [UserRole::ADMIN, UserRole::ORGA])) {
            abort(403, 'Du hast keine Berechtigung, dieses Match zu löschen.');
        }

        $match->delete();

        return response()->json([
            'message' => 'Match wurde gelöscht.'
        ]);
    }

    /**
     * EXTRA METHODE: Triggert den Faceit-Sync manuell aus dem Frontend
     * POST /matches/sync
     */
    public function sync(Request $request): JsonResponse
    {
        if (!in_array($request->user()->role, [UserRole::ADMIN, UserRole::ORGA])) {
            abort(403, 'Du darfst keinen API-Sync erzwingen.');
        }

        // Wir rufen programmatisch den Artisan Command auf, den wir vorhin gebaut haben!
        Artisan::call('app:sync-faceit-matches');

        return response()->json([
            'message' => 'Faceit-Synchronisation wurde erfolgreich im Hintergrund ausgeführt!'
        ]);
    }
}
