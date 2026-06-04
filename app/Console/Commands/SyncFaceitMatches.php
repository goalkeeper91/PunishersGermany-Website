<?php

namespace App\Console\Commands;

use App\Models\Team;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:sync-faceit-matches')]
#[Description('Command description')]
class SyncFaceitMatches extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Schön lesbare Ausgabe im Terminal starten
        $this->info('======================================');
        $this->info(' Starte Faceit Match-Synchronisation  ');
        $this->info('======================================');

        // Alle Teams aus der Datenbank holen, die eine Faceit-ID eingetragen haben
        $teams = Team::whereNotNull('faceit_id')->get();

        if ($teams->isEmpty()) {
            $this->comment('Keine Teams mit einer Faceit-ID in der Datenbank gefunden.');
            return Command::SUCCESS;
        }

        // Wir erstellen eine kleine Fortschrittsanzeige (Progress Bar) im Terminal
        $bar = $this->output->createProgressBar($teams->count());
        $bar->start();

        foreach ($teams as $team) {
            // Das Team-Model übernimmt die Arbeit und ruft den Service auf
            $team->syncMatches();

            $bar->advance();
        }

        $bar->finish();

        $this->newLine(2);
        $this->info('✅ Alle Teams erfolgreich synchronisiert!');
        $this->info('======================================');

        return Command::SUCCESS;
    }
}
