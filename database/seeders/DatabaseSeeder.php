<?php

namespace Database\Seeders;

use App\Enums\StatutRecu;
use App\Models\Depense;
use App\Models\Recu;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $recu1 = Recu::factory()->for($user)->create([
            'texte_brut' => "Boulangerie Aziz\nPain 1 x 10 MAD\nCroissant 2 x 8 MAD\nTotal 26 MAD",
            'statut' => StatutRecu::Traite,
            'total_estime' => 26,
            'devise' => 'MAD',
            'payload_brut' => [
                'articles' => [
                    ['libellé' => 'Pain', 'quantité' => 1, 'prix_unitaire' => 10, 'catégorie' => 'alimentaire'],
                    ['libellé' => 'Croissant', 'quantité' => 2, 'prix_unitaire' => 8, 'catégorie' => 'alimentaire'],
                ],
                'total_estimé' => 26,
                'devise' => 'MAD',
            ],
        ]);

        Depense::create([
            'recu_id' => $recu1->id,
            'libelle' => 'Pain',
            'quantite' => 1,
            'prix_unitaire' => 10,
            'categorie' => 'alimentaire',
        ]);

        Depense::create([
            'recu_id' => $recu1->id,
            'libelle' => 'Croissant',
            'quantite' => 2,
            'prix_unitaire' => 8,
            'categorie' => 'alimentaire',
        ]);

        $recu2 = Recu::factory()->for($user)->create([
            'texte_brut' => "Station service Al Amal\nEssence 20L x 12 MAD\nTotal 240 MAD",
            'statut' => StatutRecu::EnAttente,
            'devise' => 'MAD',
        ]);

        Depense::create([
            'recu_id' => $recu2->id,
            'libelle' => 'Essence',
            'quantite' => 20,
            'prix_unitaire' => 12,
            'categorie' => 'entretien',
        ]);

        $recu3 = Recu::factory()->for($user)->create([
            'texte_brut' => "Épicerie Sidi\nLait 2 x 7 MAD\nSucre 1 x 12 MAD\nTotal 26 MAD",
            'statut' => StatutRecu::Traite,
            'total_estime' => 26,
            'devise' => 'MAD',
            'payload_brut' => [
                'articles' => [
                    ['libellé' => 'Lait', 'quantité' => 2, 'prix_unitaire' => 7, 'catégorie' => 'alimentaire'],
                    ['libellé' => 'Sucre', 'quantité' => 1, 'prix_unitaire' => 12, 'catégorie' => 'alimentaire'],
                ],
                'total_estimé' => 26,
                'devise' => 'MAD',
            ],
        ]);

        Depense::create([
            'recu_id' => $recu3->id,
            'libelle' => 'Lait',
            'quantite' => 2,
            'prix_unitaire' => 7,
            'categorie' => 'alimentaire',
        ]);

        Depense::create([
            'recu_id' => $recu3->id,
            'libelle' => 'Sucre',
            'quantite' => 1,
            'prix_unitaire' => 12,
            'categorie' => 'alimentaire',
        ]);
    }
}
