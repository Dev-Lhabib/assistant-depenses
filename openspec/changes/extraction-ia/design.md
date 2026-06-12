## Design Overview

This design implements the Job, enums, service layer, and schema contract for receipt extraction (US6–US7). The Job orchestrates async AI extraction, persists depenses with enum-validated categories, and handles errors gracefully without crashing.

## Component Structure

### 1. Enums (PHP 8.3 Backed)

**app/Enums/StatutRecu.php**
```php
enum StatutRecu: string {
    case EnAttente = 'en_attente';
    case Traite = 'traite';
    case Echoue = 'echoue';
    
    public function label(): string {
        return match($this) {
            StatutRecu::EnAttente => 'En attente',
            StatutRecu::Traite => 'Traité',
            StatutRecu::Echoue => 'Échoué',
        };
    }
}
```

**app/Enums/CategorieDepense.php**
```php
enum CategorieDepense: string {
    case Alimentaire = 'alimentaire';
    case Boissons = 'boissons';
    case Hygiene = 'hygiene';
    case Entretien = 'entretien';
    case Autre = 'autre';
    
    public function label(): string {
        return match($this) {
            CategorieDepense::Alimentaire => 'Alimentaire',
            CategorieDepense::Boissons => 'Boissons',
            CategorieDepense::Hygiene => 'Hygiène',
            CategorieDepense::Entretien => 'Entretien',
            CategorieDepense::Autre => 'Autre',
        };
    }
}
```

### 2. AI Contract

**app/Contracts/RecuContract.php**
```php
class RecuContract {
    public static function schema(): array {
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
                                'enum' => ['alimentaire', 'boissons', 'hygiène', 'entretien', 'autre'],
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
```

### 3. Extraction Service

**app/Services/ExtractionService.php**
- Method: `public static function extract(Recu $recu): array`
- Builds French prompt from `$recu->texte_brut`
- Calls `AI::provider('groq')->text(..., RecuContract::schema())`
- Returns parsed structured output (articles array, total_estimé, devise)
- Does NOT catch exceptions — lets Job handle them

### 4. Job (Core Async Worker)

**app/Jobs/ExtraireDepensesDuRecu.php**
```php
class ExtraireDepensesDuRecu implements ShouldQueue {
    public int $tries = 1;
    
    public function __construct(public Recu $recu) {}
    
    public function handle(): void {
        try {
            // 1. Extract via ExtractionService
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
            
            // 3. Save payload and total, set status to Traite
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
                'erreur_traitement' => 'Extraction failed: ' . $e->getMessage(),
            ]);
        }
    }
}
```

### 5. Models (Updated)

**app/Models/Recu**
```php
protected $casts = [
    'statut' => StatutRecu::class,
    'payload_brut' => 'array',
    'total_estime' => 'decimal:2',
];
```

**app/Models/Depense**
```php
protected $casts = [
    'categorie' => CategorieDepense::class,
    'prix_unitaire' => 'decimal:2',
    'quantite' => 'integer',
];
```

### 6. Controller Updates

**RecuController@store()**
```php
public function store(StoreRecuRequest $request) {
    $recu = auth()->user()->recus()->create([
        'texte_brut' => $request->validated()['texte_brut'],
        'statut' => StatutRecu::EnAttente,
    ]);
    
    ExtraireDepensesDuRecu::dispatch($recu);
    
    return redirect()->route('recus.index')->with('success', 'Reçu soumis avec succès.');
}
```

**app/Http/Controllers/DepenseController@index()**
```php
public function index(Request $request): View {
    $query = auth()->user()
        ->recus()
        ->with('depenses')
        ->where('statut', StatutRecu::Traite);
    
    if ($categorie = $request->query('categorie')) {
        $query->whereHas('depenses', fn ($q) => $q->where('categorie', $categorie));
    }
    
    $depenses = $query->get()->flatMap->depenses;
    $categories = CategorieDepense::cases();
    
    return view('depenses.index', compact('depenses', 'categories'));
}
```

## Queue Dispatch

- Job dispatched immediately in `RecuController@store()`
- Uses Laravel Queue (database driver via `config/queue.php`)
- Run locally: `php artisan queue:work --tries=1 --timeout=60`
- No Docker worker container; all jobs processed by local artisan command

## Error Handling

- On exception: Log error, set `statut = StatutRecu::Echoue`, store readable message in `erreur_traitement`
- Message shown on receipt detail view (Blade template checks for `$recu->erreur_traitement`)
- HTTP response succeeds (redirect with success flash) — async errors do NOT block user

## N+1 Prevention

- `RecuController@index()` uses `withCount('depenses')`
- `DepenseController@index()` uses `with('depenses')`
- Validated with Debugbar in dev mode
