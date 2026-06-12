<?php

namespace App\Jobs;

use App\Enums\StatutRecu;
use App\Models\Depense;
use App\Models\Recu;
use App\Services\ExtractionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ExtraireDepensesDuRecu implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public Recu $recu)
    {
    }

    public function handle(): void
    {
        try {
            // 1. Extract from Groq API
            $extraction = ExtractionService::extract($this->recu);

            // 2. Create Depense records
            foreach ($extraction['articles'] as $article) {
                Depense::create([
                    'recu_id' => $this->recu->id,
                    'libelle' => $article['libellé'],
                    'quantite' => $article['quantité'],
                    'prix_unitaire' => $article['prix_unitaire'],
                    'categorie' => $article['catégorie'],  // Enum cast validates
                ]);
            }

            // 3. Save payload, total, and mark as processed
            $this->recu->update([
                'payload_brut' => $extraction,
                'total_estime' => $extraction['total_estimé'],
                'devise' => $extraction['devise'] ?? 'MAD',
                'statut' => StatutRecu::Traite,
            ]);
        } catch (\Throwable $e) {
            Log::error('ExtraireDepensesDuRecu failed', [
                'recu_id' => $this->recu->id,
                'error' => $e->getMessage(),
            ]);

            $this->recu->update([
                'statut' => StatutRecu::Echoue,
                'erreur_traitement' => 'Extraction failed: '.$e->getMessage(),
            ]);
        }
    }
}
