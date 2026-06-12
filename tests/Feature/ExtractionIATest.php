<?php

namespace Tests\Feature;

use App\Enums\StatutRecu;
use App\Jobs\ExtraireDepensesDuRecu;
use App\Models\Recu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Ai;
use Laravel\Ai\StructuredAnonymousAgent;
use Tests\TestCase;

class ExtractionIATest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_extraction_creates_depenses_and_sets_status_to_processed(): void
    {
        $user = User::factory()->create();
        $recu = Recu::factory()->for($user)->create([
            'texte_brut' => 'Sucre 2kg @ 50 MAD/kg, Huile 1L @ 120 MAD, Total: 220 MAD',
            'statut' => StatutRecu::EnAttente,
        ]);

        $fakeResponse = [
            'articles' => [
                [
                    'libellé' => 'Sucre',
                    'quantité' => 2,
                    'prix_unitaire' => 50,
                    'catégorie' => 'alimentaire',
                ],
                [
                    'libellé' => 'Huile',
                    'quantité' => 1,
                    'prix_unitaire' => 120,
                    'catégorie' => 'alimentaire',
                ],
            ],
            'total_estimé' => 220,
            'devise' => 'MAD',
        ];

        Ai::fakeAgent(StructuredAnonymousAgent::class, [$fakeResponse]);

        ExtraireDepensesDuRecu::dispatch($recu);

        $recu->refresh();

        // Assert depenses created
        $this->assertDatabaseHas('depenses', [
            'recu_id' => $recu->id,
            'libelle' => 'Sucre',
            'quantite' => 2,
            'prix_unitaire' => 50.00,
            'categorie' => 'alimentaire',
        ]);

        $this->assertDatabaseHas('depenses', [
            'recu_id' => $recu->id,
            'libelle' => 'Huile',
            'quantite' => 1,
            'prix_unitaire' => 120.00,
            'categorie' => 'alimentaire',
        ]);

        // Assert status and payload
        $this->assertEquals(StatutRecu::Traite, $recu->statut);
        $this->assertEquals(220, $recu->total_estime);
        $this->assertEquals('MAD', $recu->devise);
        $this->assertEquals($fakResponse, $recu->payload_brut);
    }

    /** @test */
    public function test_extraction_sets_status_to_failed_on_exception(): void
    {
        $user = User::factory()->create();
        $recu = Recu::factory()->for($user)->create([
            'texte_brut' => 'Invalid receipt',
            'statut' => StatutRecu::EnAttente,
        ]);

        Ai::fakeAgent(StructuredAnonymousAgent::class, function () {
            throw new \Exception('API timeout');
        });

        ExtraireDepensesDuRecu::dispatch($recu);

        $recu->refresh();

        $this->assertEquals(StatutRecu::Echoue, $recu->statut);
        $this->assertNotEmpty($recu->erreur_traitement);
        $this->assertStringContainsString('API timeout', $recu->erreur_traitement);
    }
}
