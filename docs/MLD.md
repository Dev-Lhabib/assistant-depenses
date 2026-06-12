# MLD — Assistant Dépenses

## Table UTILISATEURS

| Attribut | Type | Contraintes |
|-----------|------|-------------|
| id | PK | AUTO_INCREMENT |
| nom | VARCHAR(255) | NOT NULL |
| email | VARCHAR(255) | UNIQUE, NOT NULL |
| mot_de_passe | VARCHAR(255) | NOT NULL |

---

## Table RECUS

| Attribut | Type | Contraintes |
|-----------|------|-------------|
| id | PK | AUTO_INCREMENT |
| user_id | FK | REFERENCES utilisateurs(id) |
| texte_brut | TEXT | NULL |
| image_path | VARCHAR(255) | NULL |
| statut | ENUM | pending, processed, failed |
| erreur_traitement | TEXT | NULL |
| payload_brut | JSON | NULL |
| total_estime | DECIMAL(10,2) | NULL |
| devise | VARCHAR(10) | NULL |

---

## Table DEPENSES

| Attribut | Type | Contraintes |
|-----------|------|-------------|
| id | PK | AUTO_INCREMENT |
| recu_id | FK | REFERENCES recus(id) ON DELETE CASCADE |
| libelle | VARCHAR(255) | NOT NULL |
| quantite | INTEGER | NOT NULL |
| prix_unitaire | DECIMAL(10,2) | NOT NULL |
| categorie | ENUM | alimentaire, boissons, hygiene, entretien, autre |

---

# Relations

UTILISATEUR (1,1) ←→ (0,n) RECU

RECU (1,1) ←→ (0,n) DEPENSE

---

# Traduction Laravel

## Recu

- belongsTo(User::class)
- hasMany(Depense::class)

## Depense

- belongsTo(Recu::class)

## User

- hasMany(Recu::class)

---

# Contraintes métier

- Un reçu appartient à un seul utilisateur.
- Un utilisateur peut posséder plusieurs reçus.
- Une dépense appartient à un seul reçu.
- Un reçu peut contenir plusieurs dépenses.
- La suppression d'un reçu supprime automatiquement toutes ses dépenses.
- Le statut d'un reçu est géré uniquement par le système.
- Les catégories de dépenses sont limitées aux valeurs de l'énumération.