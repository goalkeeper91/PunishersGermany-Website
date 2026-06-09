<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    /**
     * Gibt eine Liste aller Teams zurück (Öffentlich)
     */
    public function index(): JsonResponse
    {
        // Wir laden die Teams und zählen direkt die Member mit (optimiert für React)
        $teams = Team::withCount('members')->get();

        return response()->json($teams);
    }

    /**
     * Erstellt ein neues Team (Nur Admin/Orga)
     */
    public function store(Request $request): JsonResponse
    {
        // Serverseitiger Schutz vor unberechtigten API-Aufrufen
        if (!in_array($request->user()->role, [UserRole::ADMIN, UserRole::ORGA])) {
            abort(403, 'Du hast keine Berechtigung, ein Team zu erstellen.');
        }

        // Validierung der reinkommenden Daten vom Frontend
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:teams,name',
            'slug' => 'required|string|max:255|unique:teams,slug',
            'game' => 'required|string|max:255',
            'logo' => 'nullable|string',
            'description' => 'nullable|string',
            'faceit_id' => 'nullable|string',
        ]);

        $team = Team::create($validated);

        return response()->json([
            'message' => 'Team erfolgreich erstellt!',
            'team' => $team
        ], 201);
    }

    /**
     * Zeigt die Details eines spezifischen Teams (Öffentlich)
     * Durch (Team $team) sucht Laravel das Team automatisch anhand der ID in der URL!
     */
    public function show(Team $team): JsonResponse
    {
        // Wir laden direkt die Member, den Leader und Coach mit für die Team-Seite
        return response()->json($team->load(['members', 'leader', 'coach', 'gameMatches']));
    }

    /**
     * Aktualisiert ein Team, z.B. die Faceit-ID (Nur Admin/Orga)
     */
    public function update(Request $request, Team $team): JsonResponse
    {
        // Schutz: Nur Admins oder Orga-Leiter dürfen Teams modifizieren
        if (!in_array($request->user()->role, [UserRole::ADMIN, UserRole::ORGA])) {
            abort(403, 'Du hast keine Berechtigung, dieses Team zu bearbeiten.');
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:teams,name,' . $team->id,
            'slug' => 'sometimes|required|string|max:255|unique:teams,slug,' . $team->id,
            'game' => 'sometimes|required|string|max:255',
            'logo' => 'nullable|string',
            'description' => 'nullable|string',
            'faceit_id' => 'nullable|string',
            'leader_id' => 'nullable|exists:users,id',
            'coach_id' => 'nullable|exists:users,id',
        ]);

        $team->update($validated);

        // Falls eine neue Faceit-ID eingetragen wurde, triggern wir SOFORT 
        // den ersten Match-Sync, damit das Frontend direkt Daten hat!
        if ($team->wasChanged('faceit_id') && $team->faceit_id) {
            $team->syncMatches();
        }

        return response()->json([
            'message' => 'Team erfolgreich aktualisiert!',
            'team' => $team
        ]);
    }

    /**
     * Löscht ein Team aus der Orga (Nur Admin)
     */
    public function destroy(Request $request, Team $team): JsonResponse
    {
        // Das Löschen von Teams erlauben wir sicherheitshalber NUR dem Full-Admin
        if ($request->user()->role !== UserRole::ADMIN) {
            abort(403, 'Nur Administratoren dürfen Teams löschen.');
        }

        $team->delete();

        return response()->json([
            'message' => 'Team wurde erfolgreich gelöscht.'
        ]);
    }
}
