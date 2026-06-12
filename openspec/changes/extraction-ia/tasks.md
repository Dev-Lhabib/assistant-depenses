# Tasks: extraction-ia feature implementation

## Phase 1: Foundation (Enums & Contracts)

### Task 1: Create StatutRecu enum
**File:** `app/Enums/StatutRecu.php`
**Implements:** PHP 8.3 backed enum with label() method
**Cases:** EnAttente ('en_attente'), Traite ('traite'), Echoue ('echoue')
**Verify:** `php artisan tinker` → `StatutRecu::Traite->label()` returns "Traité"

### Task 2: Create CategorieDepense enum
**File:** `app/Enums/CategorieDepense.php`
**Implements:** PHP 8.3 backed enum with label() method
**Cases:** Alimentaire, Boissons, Hygiene, Entretien, Autre
**Verify:** `CategorieDepense::Hygiene->label()` returns "Hygiène"

### Task 3: Create RecuContract schema
**File:** `app/Contracts/RecuContract.php`
**Method:** `public static function schema(): array`
**Schema:** articles array (libellé, quantité, prix_unitaire, catégorie), total_estimé, devise
**Verify:** `RecuContract::schema()` returns array with nested object properties

## Phase 2: Service Layer

### Task 4: Create ExtractionService
**File:** `app/Services/ExtractionService.php`
**Method:** `public static function extract(Recu $recu): array`
**Implementation:**
- Build French prompt from `$recu->texte_brut`
- Call `AI::provider('groq')->text(..., schema: RecuContract::schema())`
- Extract and return `$response->structured` array
- Do NOT catch exceptions (Job handles them)
**Verify:** Local test with sample receipt text

## Phase 3: Job & Queue

### Task 5: Create ExtraireDepensesDuRecu Job
**File:** `app/Jobs/ExtraireDepensesDuRecu.php`
**Implements:** `ShouldQueue`
**Configuration:** `public int $tries = 1;`
**Constructor:** Accepts `Recu $recu`
**Handle Method:**
1. Call `ExtractionService::extract($this->recu)` → $extraction
2. Loop `$extraction['articles']` and create Depense records
3. Update Recu: `payload_brut`, `total_estime`, `devise`, `statut = StatutRecu::Traite`
4. Exception handler: Log error, set status to Echoue, save erreur_traitement message
**Verify:** Dispatch test with AI::fake()

## Phase 4: Model Updates

### Task 6: Update Recu model casts
**File:** `app/Models/Recu.php`
**Add:** `'statut' => StatutRecu::class` to $casts array
**Verify:** `$recu->statut instanceof StatutRecu` returns true

### Task 7: Update Depense model
**File:** `app/Models/Depense.php`
**Fix $fillable:** Change 'libele' → 'libelle', 'quatite' → 'quantite'
**Add $casts:** 
- `'categorie' => CategorieDepense::class`
- `'prix_unitaire' => 'decimal:2'`
- `'quantite' => 'integer'`
**Verify:** Model fillable matches database column names

## Phase 5: Database Fixes

### Task 8: Fix recus migration typos
**File:** `database/migrations/2026_06_11_090629_create_recus_table.php`
**Changes:**
- Line with default statut: Change `'en_ettente'` → `'en_attente'`
- Make texte_brut nullable: `$table->string('texte_brut')->nullable();`
- Make image_path nullable: `$table->string('image_path')->nullable();`
**Verify:** Rollback and re-migrate without errors

### Task 9: Fix depenses migration column names
**File:** `database/migrations/2026_06_11_090721_create_depenses_table.php`
**Changes:**
- Rename column: `'libele'` → `'libelle'`
- Rename column: `'quatite'` → `'quantite'`
**Command:** Create new migration to rename columns (Laravel migration helper)
**Verify:** `php artisan migrate` succeeds, `depenses` table has correct column names

## Phase 6: Controller & Routes

### Task 10: Update RecuController@store()
**File:** `app/Http/Controllers/RecuController.php`
**Changes:**
- Change statut from raw string `'en_attente'` to `StatutRecu::EnAttente`
- Add: `ExtraireDepensesDuRecu::dispatch($recu);` after create
**Verify:** Form submission redirects to recus.index with success flash

### Task 11: Create DepenseController
**File:** `app/Http/Controllers/DepenseController.php`
**Method:** `index(Request $request): View`
**Implementation:**
- Eager-load: `auth()->user()->recus()->with('depenses')->where('statut', StatutRecu::Traite)`
- Optional filter: `?categorie={enum_value}` via whereHas
- Return: `view('depenses.index', ['depenses' => $depenses, 'categories' => CategorieDepense::cases()])`
**Verify:** Zero N+1 queries in Debugbar

### Task 12: Add depenses route
**File:** `routes/web.php`
**Add:** `Route::get('/depenses', [DepenseController::class, 'index'])->middleware('auth')->name('depenses.index');`
**Verify:** Route registered via `php artisan route:list | grep depenses`

## Phase 7: Request Validation (Bonus B1)

### Task 13: Create StoreRecuImageRequest
**File:** `app/Http/Requests/StoreRecuImageRequest.php`
**Rules:**
```php
return [
    'image' => 'required|image|mimes:jpg,jpeg,png|max:5120',
];
```
**Verify:** Validation rules defined (not used until B1 feature)

## Phase 8: Views

### Task 14: Update views for enum labels
**Files:** `resources/views/recus/index.blade.php`, `resources/views/recus/show.blade.php`
**Changes:**
- Fix field name: Change `texte_source` → `texte_brut`
- Change statut display: Replace raw string checks with enum: `$recu->statut->label()`
- Add error message display on detail: Show `$recu->erreur_traitement` if `$recu->statut === StatutRecu::Echoue`
**Verify:** Views render without template errors

### Task 15: Create depenses index view
**File:** `resources/views/depenses/index.blade.php`
**Display:** Libelle, quantite, prix_unitaire, categorie (with label()), recu link
**Features:** Category filter dropdown (CategorieDepense::cases())
**Styling:** Tailwind CSS consistent with existing views
**Verify:** View renders expense list with correct enum labels and category filter

## Phase 9: Testing (Bonus B2)

### Task 16: Create ExtractionIATest
**File:** `tests/Feature/ExtractionIATest.php`
**Class:** Extends `Tests\TestCase`, uses `RefreshDatabase` trait
**Test 1:** `test_extraction_creates_depenses_and_sets_status_to_processed()`
- Create test receipt with sample Darija text
- Mock: `AI::fake([...structured response matching RecuContract...])` 
- Dispatch: `ExtraireDepensesDuRecu::dispatch($recu)`
- Assert: `assertDatabaseHas('depenses', ...)` and `$recu->statut === StatutRecu::Traite`
**Test 2:** `test_extraction_sets_status_to_failed_on_exception()`
- Mock: `AI::fake()` throws exception
- Assert: `$recu->statut === StatutRecu::Echoue` and `erreur_traitement` is not empty
**Verify:** `php artisan test ExtractionIATest --verbose` passes both tests

## Phase 10: Final Integration

### Task 17: Run migrations
**Command:** `php artisan migrate` (rollback first if needed: `php artisan migrate:rollback`)
**Verify:** All tables created with correct schema (no typos in column names)

### Task 18: Queue worker setup
**Command:** Start in separate terminal: `php artisan queue:work --tries=1 --timeout=60`
**Verify:** Worker logs job processing without errors

### Task 19: Manual testing
**Steps:**
1. Register test user (login via Breeze)
2. Navigate to `/recus/create`
3. Submit sample receipt text in French/Darija
4. Verify: Receipt created with `statut = en_attente` (EN ATTENTE badge)
5. Wait ~2–5 seconds for queue worker to process
6. Refresh page: Status should change to `traite` (TRAITÉ badge) with depenses listed
7. Navigate to `/depenses`
8. Verify: Expense list shows all items with correct categories, optional filter works
9. Test error case: Mock AI failure (modify ExtractionService temporarily) and verify `statut = echoue` (ÉCHOUÉ badge) with error message on detail page
**Verify:** Feature works end-to-end

## Quality Checks

- [ ] No N+1 queries in list views (check Debugbar)
- [ ] All enum comparisons use `StatutRecu::class`, `CategorieDepense::class` (never raw strings)
- [ ] Migrations rollback cleanly: `php artisan migrate:rollback` then re-run
- [ ] Tests pass: `php artisan test` with AI::fake()
- [ ] No hardcoded Groq API key (always `config('ai.providers.groq.api_key')`)
- [ ] All exceptions logged and `erreur_traitement` set (no silent failures)
- [ ] Views use `.label()` method for enum display
- [ ] Blade templates reference correct model field names (`texte_brut`, not `texte_source`)
