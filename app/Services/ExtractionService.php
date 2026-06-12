<?php

namespace App\Services;

use App\Contracts\RecuContract;
use App\Models\Recu;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\StructuredAnonymousAgent;

class ExtractionService
{
    public static function extract(Recu $recu): array
    {
        $prompt = <<<'PROMPT'
Extrait les articles et le total estimé de ce reçu en Darija/Français/Arabe.

Reçu:
%s

Réponds UNIQUEMENT avec un JSON conforme au schéma fourni. Assure-toi que:
- Chaque article a: libellé, quantité, prix_unitaire, catégorie
- Catégorie est l'une de: alimentaire, boissons, hygiene, entretien, autre
- total_estimé est la somme de (quantité × prix_unitaire)
- devise est spécifiée
PROMPT;

        $agent = new StructuredAnonymousAgent(
            sprintf($prompt, $recu->texte_brut),
            [],
            [],
            static fn (JsonSchema $schema): array => RecuContract::schema(),
        );

        $response = $agent->prompt(
            sprintf($prompt, $recu->texte_brut),
            [],
            'groq',
        );

        return $response->structured;
    }
}
