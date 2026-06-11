# AGENTS.md — Assistant Dépenses

> Instructions for every AI agent (Claude Code, OpenCode, Cursor, Copilot) working on this project.
> Read this file completely before writing any code, generating any proposal, or modifying any file.

<!-- OPENSPEC:START -->
## OpenSpec Instructions

Always open `openspec/project.md` when the request:
- Mentions planning, proposals, specs, or a new feature
- Introduces new capabilities, breaking changes, or architecture shifts
- Sounds ambiguous and you need the authoritative spec before coding

Use `openspec/project.md` to learn:
- Stack versions, models, enums, routing conventions
- Hard architecture rules (especially: no AI calls in controllers)
- Domain knowledge (receipt text in darija, JSON contract, queue workflow)

Keep this managed block so `openspec update` can refresh the instructions.
<!-- OPENSPEC:END -->

---

## Project in One Sentence

Laravel 13 app that extracts structured expense data from raw supplier receipts (darija / French / Arabic)
using Groq AI via the official `laravel/ai` SDK, processed asynchronously via Laravel Queue.

---

## Non-Negotiable Architecture Rules

Read these before every code generation. Violating any of these is a hard error.

| # | Rule | Why |
|---|---|---|
| 1 | AI calls live **only** inside `app/Jobs/ExtraireDepensesDuRecu.php` | HTTP must not block waiting for Groq |
| 2 | Form validation lives **only** in Form Request classes | Clean controller, validated before Job dispatch |
| 3 | Enums are PHP 8.1 backed enums with Eloquent casts | Never raw string comparisons on statut or categorie |
| 4 | List views always eager-load relationships | Zero N+1 — Debugbar is installed and will catch it |
| 5 | Structured output only via `laravel/ai` | `json_decode` on free text will crash — Si Brahim's data must always be valid |
| 6 | Every DB query scoped to `auth()->id()` | No cross-user data leaks |
| 7 | Cascade delete on `recus` → `depenses` | Deleting a receipt deletes all its expense lines |

---

## What You Must Never Generate

- An AI/HTTP call inside a Controller method
- `$request->validate([...])` inline in a Controller — always use a Form Request class
- Raw string comparison on enum fields: `$recu->statut === 'pending'` ❌ → `$recu->statut === StatutRecu::Pending` ✅
- A database query inside a Blade view
- The Groq API key hardcoded anywhere in code — always read from `config('ai.providers.groq.api_key')`
- A `json_decode()` on the raw AI response — use structured output
- Silent exception catch without `Log::error()` and setting `statut = StatutRecu::Failed`

---

## Key Files — Know These Before Touching Anything

```
app/
  Contracts/
    RecuContract.php            ← JSON schema definition (reused by Job and tests)
  Enums/
    StatutRecu.php              ← pending | processed | failed (with label() method)
    CategorieDepense.php        ← alimentaire | boissons | hygiene | entretien | autre (with label())
  Http/
    Controllers/
      RecuController.php        ← index, create, store, show, destroy
      DepenseController.php     ← index (with ?categorie= filter)
    Requests/
      StoreRecuRequest.php      ← validates texte_brut: required|string|min:20|max:5000
      StoreRecuImageRequest.php ← validates image: mimes:jpg,jpeg,png|max:5120 (bonus B1)
  Jobs/
    ExtraireDepensesDuRecu.php  ← THE AI JOB — $tries=1, catch→failed, success→processed
  Models/
    Recu.php                    ← hasMany(Depense), belongsTo(User), casts: statut/payload_brut, image_path (nullable)
    Depense.php                 ← belongsTo(Recu), cast: categorie
  Services/
    ExtractionService.php       ← builds prompt + calls AI::structured()
openspec/
  project.md                    ← full domain context, read before every proposal
  config.yaml                   ← rules injected into every OpenSpec artifact
  specs/                        ← source of truth per domain
  changes/                      ← one folder per active feature
tests/
  Feature/
    ExtractionIATest.php        ← PHPUnit test with AI::fake() (bonus B2)
```

---

## Eloquent Casts Reference

```php
// Recu.php
protected $casts = [
    'statut'       => StatutRecu::class,
    'payload_brut' => 'array',
    'total_estime' => 'decimal:2',
];

// Depense.php
protected $casts = [
    'categorie'     => CategorieDepense::class,
    'prix_unitaire' => 'decimal:2',
    'quantite'      => 'integer',
];
```

---

## Job Skeleton — Do Not Deviate From This Structure

```php
class ExtraireDepensesDuRecu implements ShouldQueue
{
    public int $tries = 1;

    public function __construct(public Recu $recu) {}

    public function handle(): void
    {
        try {
            // 1. Build prompt with $this->recu->texte_brut
            // 2. Call AI::structured(...) with JSON schema
            // 3. Create one Depense per article in response
            // 4. Save payload_brut and total_estime on Recu
            // 5. Set statut = StatutRecu::Processed
            $this->recu->save();
        } catch (\Throwable $e) {
            Log::error('ExtraireDepensesDuRecu failed', [
                'recu_id' => $this->recu->id,
                'error'   => $e->getMessage(),
            ]);
            $this->recu->update(['statut' => StatutRecu::Failed]);
        }
    }
}
```

---

## AI JSON Contract — Never Change This Schema

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

---

## OpenSpec Workflow — Follow This for Every Feature

```
1. /opsx:propose <feature-name>    ← Plan mode: generates proposal + specs + design + tasks
2. Review artifacts in openspec/changes/<feature-name>/
3. /opsx:apply <feature-name>      ← Build mode: implement following tasks.md
4. /opsx:archive <feature-name>    ← Merge specs into openspec/specs/, move to archive
```

**Never start coding a feature without a validated proposal.**
**Never skip to apply without reviewing the generated tasks.md.**

---

## Commit Message Format

```
feat(scope): short description [AI-assisted]
feat(scope): short description [AI-generated, reviewed]
fix(scope): short description [AI-assisted]
chore(scope): short description
docs(scope): short description
test(scope): short description [AI-assisted]
```

Scopes: `auth`, `recus`, `depenses`, `ia`, `queue`, `db`, `views`, `enums`, `docker`, `openspec`, `bonus`

Every commit where the agent generated or helped write code **must** include `[AI-assisted]` or `[AI-generated, reviewed]`.

---

## Testing — PHPUnit

Tests use **PHPUnit** (not Pest). All test classes extend `Tests\TestCase`.

```php
// Correct test structure
class ExtractionIATest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_extraction_cree_depenses_et_passe_statut_a_processed(): void
    {
        // AI::fake([...])
        // dispatch(new ExtraireDepensesDuRecu($recu))
        // $this->assertDatabaseHas('depenses', [...])
        // $this->assertEquals(StatutRecu::Processed, $recu->fresh()->statut)
    }

    /** @test */
    public function test_extraction_echoue_si_api_indisponible(): void
    {
        // AI::fake() throws exception
        // $this->assertEquals(StatutRecu::Failed, $recu->fresh()->statut)
    }
}
```

Run tests: `php artisan test` or `php artisan test --filter=ExtractionIATest`

---

## Git Branch Conventions

```
main                        ← stable, deployable
feature/auth                ← US1: registration, login, logout
feature/recus-crud          ← US2-5: CRUD + Blade views
feature/extraction-ia       ← US6-7: laravel/ai + Groq + structured output
feature/queue-traitement    ← US7: Job + Queue + status tracking
feature/depenses-filtre     ← US8: filterable expense list
feature/bonus-image         ← Bonus B1: image upload + vision model
```

---

## Docker Commands Reference

```bash
# Docker — only MySQL + phpMyAdmin run in containers
docker compose -f compose.yaml up -d    # start MySQL and phpMyAdmin
docker compose -f compose.yaml down     # stop containers

# Laravel runs locally — all artisan commands run on your machine
php artisan migrate
php artisan queue:work --tries=1 --timeout=60
php artisan make:job ExtraireDepensesDuRecu
php artisan make:request StoreRecuRequest
php artisan test
php artisan serve
```

---

## What Good Looks Like

**Controller store() method — correct:**
```php
public function store(StoreRecuRequest $request): RedirectResponse
{
    $recu = auth()->user()->recus()->create([
        'texte_brut' => $request->validated()['texte_brut'],
        'statut'     => StatutRecu::Pending,
    ]);
    ExtraireDepensesDuRecu::dispatch($recu);
    return redirect()->route('recus.index')
        ->with('success', 'Reçu en cours de traitement.');
}
```

**Controller index() method — correct (zero N+1):**
```php
public function index(): View
{
    $recus = auth()->user()
        ->recus()
        ->withCount('depenses')
        ->latest()
        ->get();
    return view('recus.index', compact('recus'));
}
```

---

*Last updated: Day 4 — Jeudi 11/06/2026*
*Deadline: Vendredi 12/06/2026 — 14h30*