<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Depense extends Model
{
    protected $fillable = [
        'recu_id',
        'libelle',
        'quatite',
        'prix_unitaire',
        'categorie',
    ];

    public function recu(): BelongsTo
    {
        return $this->belongsTo(Recu::class);
    }
}
