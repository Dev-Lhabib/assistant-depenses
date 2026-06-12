<?php

namespace App\Contracts;

class RecuContract
{
    public static function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'articles' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'libellé' => ['type' => 'string'],
                            'quantité' => ['type' => 'integer'],
                            'prix_unitaire' => ['type' => 'number'],
                            'catégorie' => [
                                'type' => 'string',
                                'enum' => ['alimentaire', 'boissons', 'hygiene', 'entretien', 'autre'],
                            ],
                        ],
                        'required' => ['libellé', 'quantité', 'prix_unitaire', 'catégorie'],
                    ],
                    'minItems' => 1,
                ],
                'total_estimé' => ['type' => 'number'],
                'devise' => ['type' => 'string'],
            ],
            'required' => ['articles', 'total_estimé', 'devise'],
        ];
    }
}
