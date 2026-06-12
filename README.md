# 🧾 Assistant Dépenses — Extraction Intelligente de Reçus avec Laravel & IA

> **Brief complet du projet — Document de référence pour l'agent IA et l'équipe**

---

## 📌 Vue d'ensemble

**Assistant Dépenses** est une application web Laravel conçue pour **Si Brahim**, commerçant de quartier, qui accumule des dizaines de reçus fournisseurs par mois — souvent manuscrits, en darija, mal formatés. L'application lui permet de coller le texte brut d'un reçu, et l'IA en extrait automatiquement une liste structurée de dépenses (libellé, quantité, prix unitaire, catégorie).

L'objectif central : **éliminer la saisie manuelle** et donner à Si Brahim une vision claire de ses dépenses par catégorie, dans le temps.

---

## 🎯 Problème métier résolu

| Problème | Solution apportée |
|---|---|
| Reçus en darija, mal formatés, illisibles | Extraction IA via prompt structuré (Groq) |
| Saisie manuelle fastidieuse | Soumission en un clic, traitement en background |
| Aucune vision par catégorie | Catégorisation automatique + filtre |
| Risque de JSON cassé côté IA | Structured output garanti via `laravel/ai` |
| Page qui se fige pendant l'appel IA | Queue Laravel + Job asynchrone |

---

## 🧱 Stack technique

| Couche | Technologie |
|---|---|
| Framework backend | Laravel 11+ |
| Base de données | MySQL (via Docker) |
| SDK IA | `laravel/ai` (officiel) |
| Provider IA | Groq API (`llama-3.3-70b-versatile` ou `mixtral`) |
| Queue | Laravel Queue + `database` driver (ou Redis) |
| Auth | Laravel Breeze (Blade) |
| Frontend | Blade + Tailwind CSS |
| Debug | Laravel Debugbar (contrôle N+1) |
| Conteneurisation | Docker + Docker Compose |
| Tests | Pest PHP |

---

## 🗂️ Architecture du projet

```
assistant-depenses/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/                    # Authentification (Breeze)
│   │   │   ├── RecuController.php       # CRUD reçus
│   │   │   └── DepenseController.php    # Liste/filtre dépenses
│   │   └── Requests/
│   │       └── StoreRecuRequest.php     # Validation avant appel IA
│   ├── Jobs/
│   │   └── ExtraireDepensesDuRecu.php   # Job asynchrone extraction IA
│   ├── Models/
│   │   ├── Recu.php                     # hasMany Depense + casts enum/json
│   │   └── Depense.php                  # belongsTo Recu + cast enum catégorie
│   └── Enums/
│       ├── StatutRecu.php               # pending | processed | failed
│       └── CategorieDepense.php         # alimentaire | boissons | hygiène | entretien | autre
├── database/
│   └── migrations/
│       ├── create_recus_table.php
│       └── create_depenses_table.php
├── resources/views/
│   ├── recus/
│   │   ├── index.blade.php              # Liste des reçus
│   │   ├── create.blade.php             # Formulaire soumission
│   │   └── show.blade.php              # Détail reçu + dépenses
│   └── depenses/
│       └── index.blade.php             # Liste filtrée dépenses
├── specs/                              # Dossier OpenSpec (features documentées)
│   ├── auth.md
│   ├── recus-crud.md
│   ├── extraction-ia.md
│   ├── queue-traitement.md
│   └── depenses-filtre.md
├── AGENTS.md                           # Instructions pour l'agent IA
├── docker-compose.yml
└── README.md
```

---

## 🗃️ Modèle de données

### MCD (Modèle Conceptuel de Données)

```
UTILISATEUR ──< possède >── RECU ──< contient >── DEPENSE
```

### MLD (Modèle Logique de Données)

**Table `users`**
```
id (PK) | name | email | password | timestamps
```

**Table `recus`**
```
id (PK) | user_id (FK) | texte_brut (TEXT) | statut (ENUM: pending|processed|failed) 
        | payload_brut (JSON) | total_estime (DECIMAL) | devise (VARCHAR) | timestamps
```

**Table `depenses`**
```
id (PK) | recu_id (FK) | libelle (VARCHAR) | quantite (INTEGER) 
        | prix_unitaire (DECIMAL) | categorie (ENUM) | timestamps
```

### Relations Eloquent

```php
// Recu.php
public function depenses(): HasMany
{
    return $this->hasMany(Depense::class);
}

// Depense.php
public function recu(): BelongsTo
{
    return $this->belongsTo(Recu::class);
}
```

---

## 📋 Contrat JSON — Structured Output IA

L'IA **doit toujours retourner** exactement ce schéma (garanti par `laravel/ai`) :

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
  "devise": "string (ex: MAD)"
}
```

> ⚠️ Si la réponse IA ne respecte pas ce schéma → statut `failed` sur le reçu, jamais une page blanche.

---

## 🔄 Workflow de traitement d'un reçu

```
1. Utilisateur soumet le texte du reçu (POST /recus)
      ↓
2. StoreRecuRequest valide (non-vide, min/max length)
      ↓
3. Recu créé en base avec statut = "pending"
      ↓
4. Job ExtraireDepensesDuRecu dispatché dans la queue
      ↓
5. Réponse immédiate → "Reçu en cours de traitement" (page non bloquée)
      ↓
6. Worker Laravel traite le Job en background
      ↓
7. Appel Groq via laravel/ai avec structured output
      ↓
8a. Succès → Depenses enregistrées, statut = "processed"
8b. Échec  → statut = "failed", erreur loggée
```

---

## 👤 User Stories

### Sprint 1 — Authentification & Base

| US | Description |
|---|---|
| US1 | En tant qu'utilisateur, je veux m'inscrire, me connecter et me déconnecter pour que mes reçus me soient rattachés. |

### Sprint 2 — Gestion des reçus

| US | Description |
|---|---|
| US2 | En tant qu'utilisateur connecté, je veux voir la liste de mes reçus avec leur statut (En attente / Traité / Échoué) et le nombre de dépenses extraites. |
| US3 | En tant qu'utilisateur connecté, je veux soumettre un reçu et voir immédiatement "Reçu en cours de traitement" — sans page bloquée. |
| US4 | En tant qu'utilisateur connecté, je veux voir le détail d'un reçu : texte source, statut, et liste des dépenses extraites. |
| US5 | En tant qu'utilisateur connecté, je veux supprimer un reçu et toutes ses dépenses associées. |

### Sprint 3 — Extraction IA & Suivi

| US | Description |
|---|---|
| US6 | En tant qu'utilisateur connecté, l'IA extrait les articles en structured output garanti, validé et sauvegardé en base. |
| US7 | En tant qu'utilisateur connecté, je vois le statut évoluer (En attente → Traité), et "Échoué" en cas de problème. |

### Sprint 4 — Suivi des dépenses

| US | Description |
|---|---|
| US8 | En tant qu'utilisateur connecté, je veux voir toutes mes dépenses avec leur catégorie et pouvoir filtrer par catégorie. |

### Sprint Bonus

| US | Description |
|---|---|
| B1 | Uploader une photo de reçu (image) au lieu de coller du texte (File Storage + modèle multimodal). |
| B2 | Test Pest utilisant le fake du SDK `laravel/ai` — sans appel Groq réel. |

---

## 🔐 Règles métier importantes

- Un reçu appartient toujours à l'utilisateur connecté (pas de cross-access).
- Un utilisateur ne peut voir/modifier/supprimer que ses propres reçus.
- La suppression d'un reçu supprime en cascade toutes ses dépenses (`onDelete('cascade')`).
- Le statut d'un reçu n'est jamais modifiable manuellement par l'utilisateur.
- L'appel IA ne se fait **jamais** dans le controller — uniquement dans le Job.
- La validation du texte soumis se fait **avant** tout dispatch de Job.

---

## ⚙️ Casts Eloquent obligatoires

```php
// Recu.php
protected $casts = [
    'statut'      => StatutRecu::class,        // enum cast
    'payload_brut' => 'array',                 // json cast
    'total_estime' => 'decimal:2',
];

// Depense.php
protected $casts = [
    'categorie'    => CategorieDepense::class, // enum cast
    'prix_unitaire' => 'decimal:2',
    'quantite'     => 'integer',
];
```

---

## 🚀 Commandes clés

```bash
# Démarrer l'environnement Docker
docker compose up -d

# Lancer les migrations
php artisan migrate

# Lancer le worker de queue
php artisan queue:work

# Créer le Job d'extraction
php artisan make:job ExtraireDepensesDuRecu

# Lancer les tests Pest
php artisan test

# Vérifier les N+1 avec Debugbar
# → Activer dans .env: DEBUGBAR_ENABLED=true
```

---

## 📅 Planning & Livrables

| Jour | Objectif |
|---|---|
| Lundi 08/06 | Setup Docker, auth Breeze, AGENTS.md, structure OpenSpec, migrations |
| Mardi 09/06 | CRUD Reçus complet (sans IA), vues Blade, form request |
| Mercredi 10/06 | Intégration laravel/ai + Groq, Job + Queue, structured output |
| Jeudi 11/06 | Suivi statut, liste dépenses filtrée, N+1 Debugbar, polish |
| Vendredi 12/06 | Bonus (image/Pest), README final, démo live — **Deadline 14h30** |

**Livrables attendus :**
- Repository GitHub (min. 15 commits, branches feature/, mentions AI)
- Dossier `specs/` avec au moins 3 features OpenSpec
- `AGENTS.md` à la racine (commité Jour 1)
- MCD & MLD
- Jira board (SCRUM)
- README.md

---

## 🌿 Branches Git

```
main
├── feature/auth
├── feature/recus-crud
├── feature/extraction-ia
├── feature/queue-traitement
├── feature/depenses-filtre
└── feature/bonus-image (optionnel)
```

**Format de commit avec mention AI :**
```
feat(recus): add StoreRecuRequest validation [AI-assisted]
feat(ia): implement ExtraireDepensesDuRecu job with laravel/ai structured output [AI-generated, reviewed]
fix(queue): handle failed job status update [AI-assisted]
```

---

## 🤖 Décisions d'architecture — Pourquoi ?

**Pourquoi la Queue + Job ?**
L'appel à l'API Groq prend 3 à 10 secondes. Sans queue, la page HTTP se fige et le timeout risque de tuer la requête. Le Job permet de répondre immédiatement à l'utilisateur et de traiter en background.

**Pourquoi le Structured Output de laravel/ai ?**
`json_decode` peut planter si l'IA retourne du texte libre. Le SDK `laravel/ai` garantit que la réponse respecte le schéma JSON défini — ou lève une exception catchable proprement.

**Pourquoi les Form Requests ?**
Valider le texte du reçu avant de dispatcher le Job évite de gaspiller un appel API Groq sur une entrée vide ou invalide.

**Pourquoi les Enum Casts ?**
Les statuts et catégories sont des valeurs fermées. Un cast Eloquent vers un enum PHP garantit que la base de données et le code restent toujours en accord — pas de string magique qui traîne.

**Pourquoi l'Eager Loading ?**
Sans `with('depenses')`, chaque affichage de reçu dans une liste déclenche une requête SQL supplémentaire (N+1). Debugbar le détecte visuellement.
