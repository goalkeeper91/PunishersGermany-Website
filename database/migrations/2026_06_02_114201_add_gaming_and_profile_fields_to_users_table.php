<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Namens-Aufteilung (Name existiert schon standardmäßig, wir nutzen ihn als Nachname)
            $table->string('first_name')->nullable()->after('name');
            $table->string('nickname')->nullable()->after('first_name');
            
            // Gaming IDs
            $table->string('steam_id')->nullable()->after('email');
            $table->string('faceit_id')->nullable()->after('steam_id');
            
            // Flexibler Speicher für Socials & Games als JSON
            $table->json('socials')->nullable()->after('faceit_id'); // ['twitch' => '...', 'instagram' => '...']
            $table->json('games')->nullable()->after('socials');     // ['cs2', 'valorant']
            
            // Profil & Rolle
            $table->string('avatar')->nullable()->after('games'); // Pfad zum Profilbild
            $table->string('role')->default('member')->after('avatar'); // z.B. admin, coach, member
            $table->string('position')->nullable()->after('role'); // z.B. IGL, AWP, Midlaner
            
            // Team-Verknüpfung (Erlaubt NULL, falls der User noch in keinem Team ist)
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete()->after('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Wichtig: Erst den Foreign Key droppen, dann die Spalten
            $table->dropForeign(['team_id']);
            
            $table->dropColumn([
                'first_name', 'nickname', 'steam_id', 'faceit_id', 
                'socials', 'games', 'avatar', 'role', 'position', 'team_id'
            ]);
        });
    }
};
