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
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name'); 
            $table->string('slug')->unique(); 
            $table->string('game'); // z.B. "CS2", "LoL"
            $table->string('logo')->nullable(); 
            $table->text('description')->nullable();
            
            // NEU: IDs für Leader und Coach (beide verweisen auf die users-tabelle)
            $table->foreignId('leader_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('coach_id')->nullable()->constrained('users')->nullOnDelete();
            
            // NEU: Gaming & Liga-Daten
            $table->string('faceit_id')->nullable();
            $table->json('league_links')->nullable(); // ['prime_league' => '...', '99damage' => '...']
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
