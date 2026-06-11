<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recu extends Model
{
    protected $fillable = [
        'user_id',
        'texte_brut',
        'image_path',
        'statut',
        'erreur_traitement',
        'payload_brut',
        'total_estime',
        'devise',
    ];

    protected $casts = [
        'payload_brut' => 'array',
        'total_estime' => 'decimal:2',
    ];

    public function user(): BelongsTo 
    {
        return $this->belongsTo(User::class);
    }

    public function depenses(): HasMany
    {
        return $this->hasMany(Depense::class);
    }
}
