# MCD — Assistant Dépenses

## Entités

### UTILISATEUR

- id
- nom
- email
- mot_de_passe

---

### RECU

- id
- texte_brut
- image_path
- statut
- erreur_traitement
- payload_brut
- total_estime
- devise

---

### DEPENSE

- id
- libelle
- quantite
- prix_unitaire
- categorie

---

## Associations

### possède

UTILISATEUR (0,n) ─── possède ─── (1,1) RECU

Règle :
- Un utilisateur peut posséder zéro, un ou plusieurs reçus.
- Un reçu appartient obligatoirement à un seul utilisateur.

---

### contient

RECU (0,n) ─── contient ─── (1,1) DEPENSE

Règle :
- Un reçu peut contenir zéro, une ou plusieurs dépenses.
- Une dépense appartient obligatoirement à un seul reçu.

---

## Représentation textuelle

+------------------+
|   UTILISATEUR    |
+------------------+
| id               |
| nom              |
| email            |
| mot_de_passe     |
+------------------+

      (0,n)
        |
      possède
        |
      (1,1)

+------------------+
|       RECU       |
+------------------+
| id               |
| texte_brut       |
| image_path       |
| statut           |
| erreur_traitement|
| payload_brut     |
| total_estime     |
| devise           |
+------------------+

      (0,n)
        |
      contient
        |
      (1,1)

+------------------+
|     DEPENSE      |
+------------------+
| id               |
| libelle          |
| quantite         |
| prix_unitaire    |
| categorie        |
+------------------+