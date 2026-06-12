<?php

namespace App\Models;

use App\Enums\CategorieDepense;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Depense extends Model
{
    protected $fillable = [
        'recu_id',
        'libelle',
        'quantite',
        'prix_unitaire',
        'categorie',
    ];

    protected $casts = [
        'categorie' => CategorieDepense::class,
        'prix_unitaire' => 'decimal:2',
        'quantite' => 'integer',
    ];

    public function recu(): BelongsTo
    {
        return $this->belongsTo(Recu::class);
    }
}
