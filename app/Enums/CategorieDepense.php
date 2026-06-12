<?php

namespace App\Enums;

enum CategorieDepense: string
{
    case Alimentaire = 'alimentaire';
    case Boissons = 'boissons';
    case Hygiene = 'hygiene';
    case Entretien = 'entretien';
    case Autre = 'autre';

    public function label(): string
    {
        return match ($this) {
            CategorieDepense::Alimentaire => 'Alimentaire',
            CategorieDepense::Boissons => 'Boissons',
            CategorieDepense::Hygiene => 'Hygiène',
            CategorieDepense::Entretien => 'Entretien',
            CategorieDepense::Autre => 'Autre',
        };
    }
}
