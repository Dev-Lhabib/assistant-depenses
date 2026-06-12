# GitHub Copilot Instructions — Assistant Dépenses

> This file is read automatically by GitHub Copilot (Chat, inline suggestions, and
> Copilot Workspace) in VS Code and on github.com. Keep it short — it's a pointer,
> not a duplicate. Full context lives in `AGENTS.md` and `openspec/project.md`.

## Before generating anything

1. Read `AGENTS.md` at the project root — it contains the non-negotiable
   architecture rules and the "what good looks like" code samples.
2. Read `openspec/project.md` for the full domain model, JSON contract, and
   directory structure.
3. If the task is a new feature, check `openspec/specs/` first — a spec may
   already exist (proposal → specs → design → tasks).

## Hard rules — never violate

- AI calls go ONLY inside `app/Jobs/ExtraireDepensesDuRecu.php`. Never suggest
  an AI/HTTP call inside a Controller.
- Validation goes ONLY inside Form Request classes (`StoreRecuRequest`,
  `StoreRecuImageRequest`). Never suggest `$request->validate()` inline.
- `statut` and `categorie` are PHP 8.3 backed enums (`StatutRecu`,
  `CategorieDepense`) cast via Eloquent `$casts`. Never compare against raw
  strings like `'echoue'` or `'alimentaire'`.
- Any list view (recus index, depenses index) must eager-load relationships
  (`->with()`, `->load()`, `->withCount()`). Zero N+1 — verified with Debugbar.
- AI responses are parsed via `laravel/ai` structured output against
  `RecuContract::schema()`. Never suggest `json_decode()` on a raw AI response.
- Every Eloquent query is scoped to `auth()->id()` (via `auth()->user()->recus()`
  or `whereHas`).
- On any exception inside the Job: `Log::error(...)`, set
  `statut = StatutRecu::Echoue`, and store a readable message in
  `erreur_traitement`.
- `texte_brut` and `image_path` on `Recu` are mutually exclusive — never
  suggest code that requires both.
- Never hardcode the Groq API key — always `config('ai.providers.groq.api_key')`.

## Stack quick reference

- PHP 8.3, Laravel 13, MySQL 8 (Docker: mysql + phpmyadmin only — run
  `php artisan serve` and `php artisan queue:work` locally, not in containers)
- `laravel/ai` SDK, Groq provider (`llama-3.3-70b-versatile`)
- Laravel Queue, `database` driver
- Breeze (Blade) + Tailwind
- Tests: **PHPUnit only** — no Pest. Extend `Tests\TestCase`, use
  `RefreshDatabase`, methods `test_*` or `/** @test */`.

## Commit messages

Format: `type(scope): description [AI-assisted]` or `[AI-generated, reviewed]`

Scopes: `auth`, `recus`, `depenses`, `ia`, `queue`, `db`, `views`, `enums`,
`requests`, `docker`, `openspec`, `bonus`

Every commit where Copilot generated or materially helped write code must
include one of the AI tags.

## Branches

`feature/auth`, `feature/recus-crud`, `feature/extraction-ia`,
`feature/queue-traitement`, `feature/depenses-filtre`, `feature/bonus-image`

One branch per feature — don't mix changes from different features in the
same PR/commit.

