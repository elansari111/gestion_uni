# 🎓 Gestion UPF — Application de Gestion des Étudiants

Application web complète développée en **PHP Procédural** dans le cadre du TP Final de Technologie Web 2 — Université Privée de Fès.

![PHP](https://img.shields.io/badge/PHP-7%2B-blue?logo=php)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange?logo=mysql)
![XAMPP](https://img.shields.io/badge/XAMPP-Compatible-red?logo=apache)
![License](https://img.shields.io/badge/Licence-Éducatif-green)

---

## ✨ Fonctionnalités

### 👔 Espace Administrateur
- Tableau de bord avec statistiques, classements et distribution des notes
- **CRUD complet** sur les étudiants (ajout, modification, suppression, fiche détail)
- Gestion des filières avec comptage d'étudiants
- Upload de **photos de profil** (JPG/PNG, max 2 Mo, validation MIME côté serveur)
- Upload de **documents PDF** par étudiant (max 5 Mo)
- Suppression sécurisée avec **transaction PDO** (rollback en cas d'erreur)
- Recherche et filtre par filière avec **pagination** (5 étudiants/page)

### 🎓 Espace Étudiant
- Profil personnel avec informations de connexion (IP, navigateur)
- Notes, mention et classement dans la filière
- Téléchargement des documents PDF uploadés par l'admin
- Changement de mot de passe sécurisé

### 🔐 Sécurité
- Authentification par sessions PHP (`$_SESSION`)
- Mots de passe hashés avec `password_hash()` / `password_verify()`
- Requêtes préparées PDO (protection injection SQL)
- `htmlspecialchars()` sur toutes les sorties (protection XSS)
- Validation MIME des fichiers côté serveur (`mime_content_type()`)
- `.htaccess` dans les dossiers uploads (blocage exécution PHP)
- Cookie `last_login` pour mémoriser le dernier identifiant (30 jours)

---

## 🗂️ Structure du projet

```
gestion_upf/
├── index.php                    ← Redirection vers login
├── login.php                    ← Page de connexion
├── login_traitement.php         ← Traitement POST connexion
├── logout.php                   ← Déconnexion + destruction session
│
├── admin/                       ← Espace Administrateur (accès restreint)
│   ├── dashboard.php
│   ├── etudiants/
│   │   ├── liste.php            ← Liste + recherche + pagination
│   │   ├── ajouter.php
│   │   ├── ajouter_traitement.php
│   │   ├── modifier.php
│   │   ├── modifier_traitement.php
│   │   ├── supprimer.php        ← Confirmation + transaction PDO
│   │   └── detail.php          ← Fiche + upload document
│   └── filieres/
│       ├── liste.php
│       ├── ajouter.php
│       └── ajouter_traitement.php
│
├── user/                        ← Espace Étudiant (accès restreint)
│   ├── profil.php
│   ├── notes.php
│   ├── documents.php
│   ├── changer_password.php
│   └── changer_password_traitement.php
│
├── config/
│   ├── database.php             ← Connexion PDO (à créer depuis .example.php)
│   └── database.example.php    ← Modèle de configuration
│
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── auth_check_admin.php
│   └── auth_check_user.php
│
├── assets/
│   └── style.css                ← Style signature UPF (#294898 / #C72C82)
│
├── uploads/
│   ├── photos/                  ← Photos de profil (gitignorées)
│   └── documents/               ← Documents PDF (gitignorés)
│
└── gestion_upf.sql              ← Base de données complète (structure + données de test)
```

---

## 🚀 Installation

### Prérequis
- XAMPP (PHP 7.4+ et MySQL 5.7+)
- Navigateur web moderne

### Étapes

**1. Cloner le dépôt**
```bash
git clone https://github.com/VOTRE_USER/gestion_upf.git
```

**2. Copier dans htdocs**
```
C:/xampp/htdocs/gestion_upf/
```

**3. Configurer la base de données**
```bash
cp config/database.example.php config/database.php
```
Modifier `config/database.php` si nécessaire (hôte, utilisateur, mot de passe).

**4. Importer la base de données**

Ouvrir **phpMyAdmin** → Créer une base `gestion_upf` → Importer `gestion_upf.sql`

**5. Générer les mots de passe hashés**

Créer un fichier `hash.php` temporaire à la racine :
```php
<?php
echo password_hash('admin123', PASSWORD_DEFAULT) . "\n";
echo password_hash('user123', PASSWORD_DEFAULT) . "\n";
```
Ouvrir `http://localhost/gestion_upf/hash.php`, copier les hashes et les mettre à jour dans phpMyAdmin :
```sql
UPDATE utilisateurs SET password = 'HASH_ICI' WHERE login = 'admin';
UPDATE utilisateurs SET password = 'HASH_ICI' WHERE login = 'alami25';
```
Supprimer `hash.php` ensuite.

**6. Accéder à l'application**
```
http://localhost/gestion_upf/
```

---

## 👤 Comptes de test

| Login | Rôle | Étudiant associé |
|-------|------|-----------------|
| `admin` | Administrateur | — |
| `alami25` | Étudiant | E001 — Youssef Alami |
| `bennani25` | Étudiant | E002 — Sara Bennani |
| `cherkaoui25` | Étudiant | E003 — Omar Cherkaoui |

---

## 🗄️ Modèle de données

| Table | Description |
|-------|-------------|
| `utilisateurs` | Comptes de connexion (admin / user) |
| `etudiants` | Données des étudiants |
| `filieres` | Filières disponibles |
| `documents` | Documents PDF uploadés |

---

## 🛠️ Technologies utilisées

| Technologie | Usage |
|-------------|-------|
| PHP 7+ (Procédural) | Backend, sessions, upload |
| PDO + MySQL | Base de données, requêtes préparées |
| HTML5 / CSS3 | Interface utilisateur |
| Google Fonts | Playfair Display + DM Sans |
| XAMPP | Environnement de développement local |

---

## 📋 Superglobales PHP utilisées

| Superglobale | Utilisation |
|--------------|-------------|
| `$_POST` | Formulaires (login, ajout, modification) |
| `$_GET` | Paramètres URL (recherche, filtre, page, msg) |
| `$_SESSION` | user_id, login, role, etudiant_id, heure_connexion |
| `$_FILES` | Upload photos et documents PDF |
| `$_SERVER` | REQUEST_METHOD, REMOTE_ADDR, HTTP_USER_AGENT |
| `$_COOKIE` | Mémorisation du dernier login (30 jours) |

