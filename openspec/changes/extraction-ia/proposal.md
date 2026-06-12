## Why

Si Brahim's receipt submission and extraction workflow (US3–US7) currently lacks the AI Job, enums, service layer, and structured output contract needed to transform raw receipt text into validated expense lines. The feature must implement the core Job (ExtraireDepensesDuRecu), guarantee schema-backed extraction via laravel/ai structured output, and ensure proper status tracking with error messaging.

## What Changes

- Create `app/Enums/StatutRecu.php` (backed enum: en_attente, traite, echoue) with label() helper
- Create `app/Enums/CategorieDepense.php` (backed enum: alimentaire, boissons, hygiene, entretien, autre) with label() helper
- Create `app/Contracts/RecuContract.php` — static schema() method defining the guaranteed JSON structure for AI responses
- Create `app/Services/ExtractionService.php` — builds the extraction prompt and calls `AI::provider('groq')->text(...)`
- Create `app/Jobs/ExtraireDepensesDuRecu.php` — orchestrates extraction, creates Depense records, handles errors gracefully (statut=Failed, erreur_traitement message)
- Create `app/Http/Requests/StoreRecuImageRequest.php` — validates image uploads (mimes: jpg,jpeg,png | max: 5120) for bonus B1
- Create `app/Http/Controllers/DepenseController.php` — index with ?categorie= filter (zero N+1 eager-load)
- Create `resources/views/depenses/index.blade.php` — filterable expense list by category
- Create `tests/Feature/ExtractionIATest.php` — PHPUnit test with AI::fake() (bonus B2)
- Fix migration typos: `recus.texte_brut` (was nullable), `depenses.libelle` & `quantite` (was `libele`, `quatite`)
- Update `app/Models/Recu` — add enums StatutRecu, CategorieDepense casts, payload_brut, total_estime fields
- Update `app/Models/Depense` — add CategorieDepense cast, fix field names
- Update `RecuController@store()` — dispatch Job, use StatutRecu::EnAttente instead of raw string
- Update `routes/web.php` — add `/depenses` route for DepenseController@index

## Capabilities

### New Capabilities
- `receipt-extraction-job`: Asynchronous Job that calls Groq via laravel/ai structured output, creates Depense records, and handles failures with error messages
- `receipt-status-enums`: Backed PHP 8.3 enums (StatutRecu, CategorieDepense) with Eloquent casts and label() helpers for UI
- `expense-filtering-ui`: Filterable expense list view with eager-loaded relationships and no N+1 queries
- `structured-ai-contract`: RecuContract providing the JSON schema that AI responses must conform to, reused in tests and Job

### Modified Capabilities
- `receipt-crud`: Existing RecuController@store() now dispatches ExtraireDepensesDuRecu Job instead of storing status inline; uses enum values

## Impact

- **Files**: 12 new, 8 modified (models, controllers, views, routes, migrations)
- **Database**: No new columns (schema already supports payload_brut, total_estime, devise, erreur_traitement, image_path)
- **Queue**: Job uses `database` driver; must be run via `php artisan queue:work`
- **N+1 Risks**: DepenseController@index must eager-load(`depenses`); RecuController@index already uses withCount
- **Texte/Image Path**: Feature touches texte_brut only (US3 submission); image_path is bonus B1 (not included in this change)
- **Dependencies**: laravel/ai (^0.8.0) already required
- **Branch**: `feature/extraction-ia` (maps to US6–US7)
