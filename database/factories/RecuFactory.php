<?php

namespace Database\Factories;

use App\Enums\StatutRecu;
use App\Models\Recu;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Recu>
 */
class RecuFactory extends Factory
{
    protected $model = Recu::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'texte_brut' => fake()->paragraph(),
            'image_path' => null,
            'statut' => StatutRecu::EnAttente,
            'erreur_traitement' => null,
            'payload_brut' => null,
            'total_estime' => null,
            'devise' => 'MAD',
        ];
    }
}
