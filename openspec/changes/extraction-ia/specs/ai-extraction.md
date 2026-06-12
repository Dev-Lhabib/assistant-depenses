## Enums: StatutRecu & CategorieDepense

### StatutRecu

**Location:** `app/Enums/StatutRecu.php`

**Cases:**
- `EnAttente = 'en_attente'` — Default when receipt text submitted
- `Traite = 'traite'` — Job successfully extracted and created depenses
- `Echoue = 'echoue'` — Job encountered exception during extraction

**Methods:**
- `label(): string` — Return French display name ("En attente", "Traité", "Échoué")

**Eloquent Cast:**
```php
// In Recu model
'statut' => StatutRecu::class,
```

**Usage:**
```php
$recu->statut = StatutRecu::EnAttente;
$recu->statut === StatutRecu::Traite  // Boolean check
$recu->statut->label()  // "Traité"
```

### CategorieDepense

**Location:** `app/Contracts/CategorieDepense.php`

**Cases:**
- `Alimentaire = 'alimentaire'`
- `Boissons = 'boissons'`
- `Hygiene = 'hygiene'`
- `Entretien = 'entretien'`
- `Autre = 'autre'`

**Methods:**
- `label(): string` — Return French display name with accents

**Eloquent Cast:**
```php
// In Depense model
'categorie' => CategorieDepense::class,
```

**Note:** Category names use French accents in schema ('hygiène'), but enum backing value uses ASCII-safe keys ('hygiene'). Label method handles display.

---

## AI Extraction Contract

### RecuContract

**Location:** `app/Contracts/RecuContract.php`

**Purpose:** Define the guaranteed JSON schema that Groq AI responses must conform to. Reused in Job and tests.

**Method:** `public static function schema(): array`

**Schema Structure:**
```json
{
  "articles": [
    {
      "libellé": "string",
      "quantité": "integer",
      "prix_unitaire": "number",
      "catégorie": "alimentaire | boissons | hygiène | entretien | autre"
    }
  ],
  "total_estimé": "number",
  "devise": "string"
}
```

**Validation:** Schema MUST be passed to `AI::structured(schema: RecuContract::schema(), ...)`

**Never Change:** This contract is the source of truth for AI parsing. Changing it breaks all Job executions and tests.

---

## Extraction Job

### ExtraireDepensesDuRecu

**Location:** `app/Jobs/ExtraireDepensesDuRecu.php`

**Implements:** `ShouldQueue`

**Constructor:**
```php
public function __construct(public Recu $recu)
```

**Configuration:**
```php
public int $tries = 1;  // No auto-retry
```

**Handle Method Flow:**

1. **Extract:** Call `ExtractionService::extract($this->recu)` → returns structured array
2. **Create Depenses:** Loop articles, create one `Depense` per item
3. **Save Metadata:** Set `payload_brut`, `total_estime`, `devise` on Recu
4. **Mark Success:** Set `statut = StatutRecu::Traite`
5. **Exception Handler:**
   - Log error with `recu_id` and exception message
   - Set `statut = StatutRecu::Echoue`
   - Store readable message in `erreur_traitement`
   - Do NOT rethrow

**Exception Handling Strategy:**
- Silently catch ALL throwables (no rethrow)
- DB query failures, AI timeouts, schema validation errors all caught the same way
- User views error message on receipt detail page, receipt index shows "Échoué" badge

---

## Extraction Service

### ExtractionService

**Location:** `app/Services/ExtractionService.php`

**Method Signature:**
```php
public static function extract(Recu $recu): array
```

**Input:** Recu model instance with `texte_brut` populated

**Process:**
1. Build French-language extraction prompt from `$recu->texte_brut`
2. Call AI::structured() with RecuContract::schema()
3. Parse response to array
4. Return: `['articles' => [...], 'total_estimé' => ..., 'devise' => ...]`

**Prompt Template (French):**
```
Extrait les articles et le total estimé de ce reçu en Darija/Français/Arabe.

Reçu:
{texte_brut}

Réponds UNIQUEMENT avec un JSON conforme à ce schéma: {...}
```

**AI Call:**
```php
$response = AI::provider('groq')->text(
    prompt: "...",
    schema: RecuContract::schema(),
);
return $response->structured;
```

**Note:** Does NOT catch exceptions — Job handles them.

---

## Request Validation

### StoreRecuImageRequest (Bonus B1)

**Location:** `app/Http/Requests/StoreRecuImageRequest.php`

**Rules:**
```php
return [
    'image' => 'required|image|mimes:jpg,jpeg,png|max:5120',  // 5MB
];
```

**Usage:** Not required for core US6–US7; prepared for image upload feature.

---

## Database Migrations (Fixes)

### Fix: recus Table

**Location:** `database/migrations/2026_06_11_090629_create_recus_table.php`

**Issues:**
- Default value typo: `'en_ettente'` should be `'en_attente'`
- Missing nullable() on text fields

**Corrections:**
```php
$table->string('texte_brut')->nullable();  // Was not nullable
$table->string('image_path')->nullable();  // Was not nullable
$table->string('statut')->default('en_attente');  // Was 'en_ettente'
```

### Fix: depenses Table

**Location:** `database/migrations/2026_06_11_090721_create_depenses_table.php`

**Issues:**
- Column name typo: `'libele'` should be `'libelle'`
- Column name typo: `'quatite'` should be `'quantite'`

**Corrections:**
```php
$table->string('libelle');  // Was 'libele'
$table->integer('quantite');  // Was 'quatite'
```

---

## Models: Updated Casts

### Recu Model

**File:** `app/Models/Recu.php`

**Add to $casts:**
```php
protected $casts = [
    'statut' => StatutRecu::class,
    'payload_brut' => 'array',
    'total_estime' => 'decimal:2',
];
```

### Depense Model

**File:** `app/Models/Depense.php`

**Add to $casts:**
```php
protected $casts = [
    'categorie' => CategorieDepense::class,
    'prix_unitaire' => 'decimal:2',
    'quantite' => 'integer',
];
```

**Fix $fillable:**
```php
protected $fillable = [
    'recu_id',
    'libelle',  // Was 'libele'
    'quantite',  // Was 'quatite'
    'prix_unitaire',
    'categorie',
];
```
