# Assistant Dépenses — Project Context

> This file is the authoritative context for all AI agents working on this project.
> Read this before generating any proposal, spec, design, or task.

---

## What This Project Is

A Laravel web application for **Si Brahim**, a neighbourhood grocery shop owner in Morocco.
He pastes raw supplier receipt text (often in darija, poorly formatted, abbreviated) or uploads
a photo, and the app automatically extracts a structured list of expenses — label, quantity,
unit price, category — that he can browse and filter over time.

**Core value proposition:** eliminate manual data entry by absorbing it into AI extraction,
with guaranteed structured output so the data is always typed and valid.

---

## Tech Stack (exact versions)

| Layer | Technology | Version |
|---|---|---|
| Language | PHP | 8.3 |
| Framework | Laravel | 13 |
| Database | MySQL | 8.0 (Docker) |
| AI SDK | laravel/ai | latest (official) |
| AI Provider | Groq API | llama-3.3-70b-versatile |
| Queue driver | database (Laravel Queue) | — |
| Auth | Laravel Breeze (Blade stack) | — |
| Frontend | Blade + Tailwind CSS | Tailwind 3 |
| Debug | barryvdh/laravel-debugbar + Telescope | dev only |
| Tests | PHPUnit | Laravel default |
| Container | Docker (MySQL + phpMyAdmin only) | — |

---

## Architecture Patterns — Non-Negotiable Rules

1. **Never call the AI API inside a Controller.** AI calls live exclusively inside Jobs.
2. **Every form submission goes through a Form Request.** No `$request->validate()` inline in controllers.
3. **All enums are PHP 8.3 backed enums with Eloquent casts.** No raw string comparisons on status or category.
4. **Every Eloquent relationship used in a list view must be eager-loaded.** Zero N+1 — enforced with Debugbar.
5. **Queue is mandatory for AI extraction.** The HTTP response must return before Groq replies.
6. **Structured output only.** The AI call uses `laravel/ai` structured output — never `json_decode` on free text.
7. **User scoping on every query.** Every controller query filters by `auth()->id()` — no cross-user data leaks.

---

## Domain Models

### Recu (Receipt)
Belongs to a User. Has many Depenses.

| Field | Type | Notes |
|---|---|---|
| id | bigint PK | |
| user_id | bigint FK | → users, required, cascade delete |
| texte_brut | text | Raw receipt text — nullable, mutually exclusive with image_path |
| image_path | varchar(255) | Path to uploaded receipt image — nullable, mutually exclusive with texte_brut (bonus B1) |
| statut | enum | en_attente / traite / echoue — cast to StatutRecu enum |
| erreur_traitement | text | Error message from Groq when statut = echoue — nullable, shown to user |
| payload_brut | json | Raw AI response — cast to array, nullable |
| total_estime | decimal(8,2) | Extracted total, nullable |
| devise | varchar(10) | Currency code, default MAD |
| timestamps | | |

### Depense (Expense line)
Belongs to a Recu.

| Field | Type | Notes |
|---|---|---|
| id | bigint PK | |
| recu_id | bigint FK | → recus, onDelete cascade |
| libelle | varchar(255) | Item label (from AI extraction) |
| quantite | integer | Quantity |
| prix_unitaire | decimal(8,2) | Unit price |
| categorie | enum | alimentaire / boissons / hygiene / entretien / autre — cast to CategorieDepense enum |
| timestamps | | |

---

## Enums

```php
// app/Enums/StatutRecu.php
enum StatutRecu: string {
    case EnAttente = 'en_attente';
    case Traite    = 'traite';
    case Echoue    = 'echoue';
    // label() method returns FR string: "En attente" / "Traité" / "Échoué"
}

// app/Enums/CategorieDepense.php
enum CategorieDepense: string {
    case Alimentaire = 'alimentaire';
    case Boissons    = 'boissons';
    case Hygiene     = 'hygiene';
    case Entretien   = 'entretien';
    case Autre       = 'autre';
    // label() method returns FR string with accent: "Alimentaire" / "Boissons" / "Hygiène" / "Entretien" / "Autre"
}
```

---

## AI Contract — Guaranteed JSON Schema

The AI **must always** return this exact structure (enforced by `laravel/ai` structured output):

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

If the AI response does not match this schema → set `statut = StatutRecu::Echoue` and
store a readable message in `erreur_traitement`. Never crash.

---

## Job: ExtraireDepensesDuRecu

- Location: `app/Jobs/ExtraireDepensesDuRecu.php`
- Receives: `Recu $recu` in constructor
- `$tries = 1` (no automatic retry)
- On success: create N `Depense` records + set `statut = StatutRecu::Traite` + save `payload_brut` + `total_estime` + `devise`
- On any exception: set `statut = StatutRecu::Echoue` + save message in `erreur_traitement` + `Log::error(...)` + do NOT rethrow

---

## Routing Conventions

All routes protected by `auth` middleware.

```
GET    /recus              → RecuController@index
GET    /recus/create       → RecuController@create
POST   /recus              → RecuController@store     (uses StoreRecuRequest or StoreRecuImageRequest)
GET    /recus/{recu}       → RecuController@show
DELETE /recus/{recu}       → RecuController@destroy
GET    /depenses           → DepenseController@index  (accepts ?categorie= filter)
```

---

## Validation Rules

**StoreRecuRequest** (text submission):
```php
'texte_brut' => ['required', 'string', 'min:20', 'max:5000']
```

**StoreRecuImageRequest** (image submission — bonus B1):
```php
'image' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120']
```

---

## Testing Convention — PHPUnit

Tests use **PHPUnit** (Laravel default). No Pest. All test classes extend `Tests\TestCase` and use `RefreshDatabase`. Test methods are prefixed with `test_` or annotated with `/** @test */`.

---

## Directory Structure

```
app/
  Contracts/
    RecuContract.php        ← JSON schema (reused by Job + tests)
  Enums/
    StatutRecu.php
    CategorieDepense.php
  Http/
    Controllers/
      RecuController.php
      DepenseController.php
    Requests/
      StoreRecuRequest.php
      StoreRecuImageRequest.php  ← bonus B1
  Jobs/
    ExtraireDepensesDuRecu.php
  Models/
    Recu.php
    Depense.php
  Services/
    ExtractionService.php       ← prompt building + AI::structured() call
resources/views/
  recus/
    index.blade.php
    create.blade.php
    show.blade.php
  depenses/
    index.blade.php
tests/
  Feature/
    ExtractionIATest.php        ← PHPUnit + AI::fake() (bonus B2)
openspec/
  project.md          ← this file
  config.yaml
  specs/
  changes/
```

---

## Git Conventions

**Branches:**
```
feature/auth
feature/recus-crud
feature/extraction-ia
feature/queue-traitement
feature/depenses-filtre
feature/bonus-image        (optional)
```

**Commit message format:**
```
feat(scope): description [AI-assisted]
feat(scope): description [AI-generated, reviewed]
fix(scope): description [AI-assisted]
```

Every commit must mention AI usage if the agent generated or helped write the code.
Minimum 15 commits total, daily commits on the active feature branch.

---

## What the Agent Must NOT Do

- Do not put AI calls in controllers
- Do not use `$request->validate()` directly in controllers — always use Form Request classes
- Do not use raw string comparisons for enums (`$recu->statut === 'echoue'` is wrong; use `$recu->statut === StatutRecu::Echoue`)
- Do not lazy-load relationships in list views
- Do not hardcode the Groq API key anywhere in code
- Do not catch exceptions silently without logging, setting `statut = StatutRecu::Echoue`, and saving `erreur_traitement`
- Do not create database queries inside Blade views
- Do not require both `texte_brut` and `image_path` on the same Recu

---

## Key Business Rules

- A receipt belongs to the authenticated user only — never expose another user's data
- Deleting a receipt cascades to all its depenses
- Receipt status is never editable by the user — only the Job changes it
- The submission page must never block — always dispatch to queue and redirect immediately
- Input language can be darija, French, Arabic, or mixed — the AI prompt must handle all three
- A Recu is submitted via texte_brut OR image_path — never both