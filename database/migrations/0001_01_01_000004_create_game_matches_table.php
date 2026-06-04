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
        Schema::create('game_matches', function (Blueprint $table) {
            $table->id();
            
            // Verknüpfung zu eurem Team
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            
            // Infos zum Gegner
            $table->string('opponent_name');
            $table->string('opponent_logo')->nullable();
            
            // Wann und Wo
            $table->dateTime('scheduled_at');
            $table->string('match_url')->nullable();
            
            // Ergebnisse
            $table->integer('team_score')->nullable();
            $table->integer('opponent_score')->nullable();
            
            // Status (scheduled, live, finished, canceled)
            $table->string('status')->default('scheduled');
            
            // Details (Maps, Lineups etc.)
            $table->json('details')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_matches');
    }
};
