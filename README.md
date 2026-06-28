# 🗳️ Vote System - Système de Vote Électronique Sécurisé

Un système de vote électronique moderne, sécurisé et scalable, conçu pour les organisations et les institutions. Plateforme complète permettant la gestion d'élections, l'authentification sécurisée, la détection de fraude et la génération de rapports.

## 📋 Table des matières

- [Caractéristiques](#caractéristiques)
- [Architecture](#architecture)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Configuration](#configuration)
- [Utilisation](#utilisation)
- [Structure du projet](#structure-du-projet)
- [API et Endpoints](#api-et-endpoints)
- [Sécurité](#sécurité)
- [Base de données](#base-de-données)
- [Développement](#développement)
- [Déploiement](#déploiement)
- [Dépannage](#dépannage)
- [Licence](#licence)

---

## ✨ Caractéristiques

### 🔐 Sécurité
- **Authentification robuste** avec hash de mot de passe (bcrypt)
- **Protection CSRF** avec tokens uniques
- **Vérification reCAPTCHA** pour prévenir les abus automatisés
- **Authentification OAuth2** (intégration Google)
- **Validation des entrées** et sanitization complète
- **Préparation des requêtes PDO** pour prévenir les injections SQL
- **Logs de sécurité** avec traçabilité complète
- **Protection contre la double-inscription** aux élections

### 🗳️ Gestion des élections
- **Création et gestion d'élections** avec statuts (draft, active, closed)
- **Gestion des candidats** avec photos et descriptions
- **Gestion des électeurs** avec vérification de l'admissibilité
- **Support multi-organisations** avec SaaS intégré
- **Périodes de vote configurables**
- **Dépôt de candidatures** avec validation

### 📊 Analyse et rapports
- **Dashboard administrateur** avec statistiques en temps réel
- **Génération de rapports PDF** via TCPDF
- **Graphiques de participation** et tendances de vote
- **Export de données** (CSV, PDF)
- **Détection de fraude** avec algorithmes avancés
- **Audit logs** complets de toutes les actions

### 📧 Communication
- **Service d'email** intégré pour les notifications
- **Files d'attente asynchrones** pour les envois en masse
- **Confirmation de vote** par email
- **Vérification d'email** avant la participation
- **Notifications de l'administrateur**

### 🌐 Expérience utilisateur
- **Interface responsive** compatible mobile
- **Dashboard électeur** pour suivre les élections
- **Dashboard administrateur** complet
- **Portail de candidature** intégré
- **Historique de vote** personnel
- **Gestion du profil utilisateur**

### ⚙️ Architecture technique
- **Architecture MVC** moderne et maintenable
- **Routeur AltoRouter** flexible et performant
- **PSR-4 Autoloading** via Composer
- **Variables d'environnement** pour la configuration
- **Singleton Database** pour gérer les connexions
- **Service Layer** pour la logique métier
- **Middleware** pour les contrôles de sécurité

---

## 🏗️ Architecture

```
┌─────────────────────────────────────────────────────┐
│           Navigateur / Client                       │
└────────────────┬────────────────────────────────────┘
                 │ HTTP/HTTPS
┌────────────────▼────────────────────────────────────┐
│     Serveur Web (Apache + mod_rewrite)              │
│              public/index.php                       │
└────────────────┬────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────┐
│           AltoRouter (Routage)                      │
├─────────────────────────────────────────────────────┤
│  Controllers (AdminController, VoteController...)   │
├─────────────────────────────────────────────────────┤
│  Models (User, Election, Vote, Candidate...)        │
├─────────────────────────────────────────────────────┤
│  Services (EmailService, FraudDetectionService...)  │
├─────────────────────────────────────────────────────┤
│  Helpers (SecurityHelper, CaptchaHelper...)         │
└────────────────┬────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────┐
│        Base de données MySQL/MariaDB                │
│  (Users, Elections, Candidates, Votes, Audits...)   │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│     Services asynchrones (File d'attente)           │
│  - Envoi d'emails                                   │
│  - Notifications                                    │
│  - Traitement des votes                             │
└─────────────────────────────────────────────────────┘
```

---

## 📦 Prérequis

- **PHP** ≥ 7.4 (Testé jusqu'à PHP 8.x)
- **MySQL** ou **MariaDB** ≥ 5.7
- **Apache** (avec mod_rewrite activé) ou Nginx
- **Composer** pour la gestion des dépendances
- **OpenSSL** pour le chiffrement
- **Extension PHP** : PDO, mbstring, ctype, JSON

### Dépendances PHP (géré par Composer)

```
- altorouter/altorouter ^2.0      # Routeur léger et performant
- vlucas/phpdotenv ^5.5           # Gestion des variables d'environnement
- tecnickcom/tcpdf ^6.10          # Génération de PDF
- league/oauth2-google ^4.1       # Authentification OAuth2 Google
```

---

## 🚀 Installation

### 1. Cloner le repository

```bash
git clone https://github.com/yourusername/vote-system.git
cd vote-system
```

### 2. Installer les dépendances

```bash
composer install
```

### 3. Créer le fichier d'environnement

```bash
cp .env.example .env
```

### 4. Configurer le fichier `.env`

```env
# Configuration de la base de données
DB_HOST=localhost
DB_NAME=vote_system
DB_USER=root
DB_PASSWORD=
DB_CHARSET=utf8mb4

# Configuration de l'application
APP_DEBUG=true
APP_URL=http://localhost/vote-system

# Configuration d'email
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_FROM=noreply@votesystem.com
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password

# reCAPTCHA
RECAPTCHA_SITE_KEY=your_recaptcha_site_key
RECAPTCHA_SECRET_KEY=your_recaptcha_secret_key

# OAuth2 Google
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost/vote-system/auth/google/callback

# Session
SESSION_LIFETIME=3600
SESSION_SECURE=false  # true en production avec HTTPS
```

### 5. Créer la base de données

```bash
php bin/run_migration.php
```

Ou manuellement :

```sql
CREATE DATABASE vote_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vote_system;
-- Importer le schéma SQL
SOURCE schema.sql;
```

### 6. Créer un utilisateur administrateur

```bash
php bin/manage_admin.php
```

### 7. Configurer le serveur web

#### Apache (.htaccess)

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /vote-system/
    
    # Rediriger vers public
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ public/index.php?path=$1 [QSA,L]
</IfModule>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name localhost;
    
    root /xampp/htdocs/vote-system/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 8. Définir les permissions

```bash
chmod -R 755 storage/
chmod -R 777 storage/logs
chmod -R 777 storage/cache
chmod -R 777 storage/sessions
chmod -R 777 public/uploads
```

---

## ⚙️ Configuration

### Variables d'environnement

| Variable | Description | Valeur par défaut |
|----------|-------------|-------------------|
| `APP_DEBUG` | Mode débogage | `false` |
| `APP_URL` | URL de l'application | `http://localhost` |
| `DB_HOST` | Hôte de la base de données | `localhost` |
| `DB_NAME` | Nom de la base de données | `vote_system` |
| `DB_USER` | Utilisateur MySQL | `root` |
| `DB_PASSWORD` | Mot de passe MySQL | `` |
| `MAIL_HOST` | Serveur SMTP | `localhost` |
| `MAIL_PORT` | Port SMTP | `587` |
| `RECAPTCHA_SITE_KEY` | Clé reCAPTCHA (site) | `` |
| `RECAPTCHA_SECRET_KEY` | Clé reCAPTCHA (secret) | `` |
| `SESSION_LIFETIME` | Durée de la session (secondes) | `3600` |

### Fichier de configuration applicatif

Voir [config/app.php](config/app.php) pour les constantes globales.

---

## 📖 Utilisation

### Pour les administrateurs

1. **Accéder au dashboard** : `/admin/dashboard`
2. **Créer une élection** :
   - Titre et description
   - Dates de vote
   - Nombre d'électeurs admissibles
   - Candidats
3. **Gérer les candidatures** :
   - Approuver/rejeter les candidatures
   - Ajouter des candidats manuellement
4. **Suivre les votes** :
   - Taux de participation en temps réel
   - Résultats provisoires
   - Détection de fraude
5. **Générer des rapports** :
   - PDF détaillé avec résultats
   - Export CSV
   - Graphiques et statistiques

### Pour les électeurs

1. **S'inscrire** : `/auth/register`
2. **Se connecter** : `/auth/login`
3. **Consulter les élections** : `/voter/dashboard`
4. **Voter** :
   - Sélectionner l'élection
   - Consulter les candidats
   - Voter pour un candidat
   - Confirmation par email
5. **Suivre ses votes** : `/voter/history`

### Pour les candidats

1. **Accéder au portail** : `/candidate/portal`
2. **Déposer sa candidature** :
   - Formulaire en ligne
   - Télécharger une photo
   - Description personnelle
3. **Suivre sa candidature** : Statut (en attente, approuvée, rejetée)

---

## 🗂️ Structure du projet

```
vote-system/
├── bin/                          # Scripts de ligne de commande
│   ├── manage_admin.php          # Gestion des administrateurs
│   ├── run_migration.php         # Exécution des migrations
│   ├── queue_worker.php          # Worker pour file d'attente
│   └── ...
│
├── config/                       # Fichiers de configuration
│   └── app.php                   # Configuration globale
│
├── public/                       # Racine web publique
│   ├── index.php                 # Point d'entrée
│   ├── .htaccess                 # Rules Apache
│   └── assets/
│       ├── css/                  # Feuilles de style
│       ├── js/                   # Scripts JavaScript
│       ├── images/               # Images statiques
│       └── icons/                # Icônes
│
├── src/                          # Code source applicatif
│   ├── Config/
│   │   └── Database.php          # Gestion de la base de données
│   │
│   ├── Controllers/              # Contrôleurs MVC
│   │   ├── AdminController.php
│   │   ├── AuthController.php
│   │   ├── VoteController.php
│   │   ├── VoterController.php
│   │   ├── CandidateController.php
│   │   └── ...
│   │
│   ├── Models/                   # Modèles de données
│   │   ├── User.php
│   │   ├── Election.php
│   │   ├── Candidate.php
│   │   ├── Vote.php
│   │   ├── Voter.php
│   │   ├── AuditLog.php
│   │   └── ...
│   │
│   ├── Services/                 # Logique métier
│   │   ├── EmailService.php      # Gestion des emails
│   │   ├── FraudDetectionService.php  # Détection de fraude
│   │   ├── ExportService.php     # Export de données
│   │   └── QueueService.php      # File d'attente
│   │
│   ├── Helpers/                  # Fonctions utilitaires
│   │   ├── SecurityHelper.php    # Sécurité (sanitization, CSRF)
│   │   ├── CaptchaHelper.php     # Vérification reCAPTCHA
│   │   └── ...
│   │
│   ├── Jobs/                     # Tâches asynchrones
│   │   ├── SendVerificationEmailJob.php
│   │   ├── SendVoteConfirmationJob.php
│   │   └── ...
│   │
│   ├── Middleware/               # Middlewares
│   └── Views/                    # Vues (templates)
│       ├── home.php
│       ├── layouts/
│       ├── admin/
│       ├── auth/
│       ├── vote/
│       ├── voter/
│       └── ...
│
├── storage/                      # Données générées
│   ├── logs/                     # Fichiers de log
│   ├── cache/                    # Fichiers en cache
│   ├── sessions/                 # Données de session
│   └── ...
│
├── vendor/                       # Dépendances (Composer)
├── .env                          # Variables d'environnement
├── .env.example                  # Exemple de .env
├── composer.json                 # Configuration Composer
├── composer.lock                 # Verrouillage des dépendances
├── .gitignore                    # Git ignore
└── README.md                     # Ce fichier
```

---

## 🔗 API et Endpoints

### Authentification

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/auth/login` | Afficher le formulaire de connexion |
| POST | `/auth/login` | Traiter la connexion |
| GET | `/auth/register` | Afficher le formulaire d'inscription |
| POST | `/auth/register` | Créer un nouveau compte |
| GET | `/auth/logout` | Se déconnecter |
| POST | `/auth/forgot-password` | Demander la réinitialisation |
| POST | `/auth/reset-password` | Réinitialiser le mot de passe |
| GET | `/auth/verify-email/:token` | Vérifier l'adresse email |

### Admin

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/admin/dashboard` | Dashboard administrateur |
| GET | `/admin/elections` | Liste des élections |
| POST | `/admin/elections/create` | Créer une élection |
| GET | `/admin/elections/:id/edit` | Éditer une élection |
| POST | `/admin/elections/:id/update` | Mettre à jour une élection |
| POST | `/admin/elections/:id/delete` | Supprimer une élection |
| GET | `/admin/elections/:id/results` | Résultats d'une élection |
| GET | `/admin/users` | Gestion des utilisateurs |
| GET | `/admin/audit-logs` | Logs de sécurité |

### Votant

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/voter/dashboard` | Dashboard votant |
| GET | `/voter/elections` | Élections disponibles |
| GET | `/voter/elections/:id` | Détails d'une élection |
| POST | `/voter/elections/:id/register` | S'inscrire à une élection |
| GET | `/voter/vote/:electionId` | Formulaire de vote |
| POST | `/voter/vote/:electionId` | Soumettre un vote |
| GET | `/voter/history` | Historique des votes |

### Candidat

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/candidate/portal` | Portail candidat |
| POST | `/candidate/apply` | Soumettre une candidature |
| GET | `/candidate/status` | Statut de candidature |

---

## 🔒 Sécurité

### Mesures de sécurité implémentées

1. **Authentification**
   - Hachage des mots de passe avec bcrypt
   - Sessions sécurisées côté serveur
   - Tokens d'authentification
   - Expiration automatique des sessions

2. **Protection CSRF**
   - Tokens CSRF générés pour chaque formulaire
   - Validation sur chaque soumission
   - Renouvellement régulier

3. **Prévention des injections**
   - Requêtes préparées PDO
   - Sanitization des entrées
   - Validation stricte des données
   - Échappement des sorties

4. **Protection anti-fraude**
   - reCAPTCHA sur authentification
   - Limite de tentatives de connexion
   - Détection d'IP suspectes
   - Vérification de double-inscription
   - Fingerprinting des navigateurs

5. **Audit et logs**
   - Traçabilité complète de tous les votes
   - Logs de sécurité pour administrateurs
   - Enregistrement des modifications

6. **Chiffrement**
   - HTTPS obligatoire en production
   - Données sensibles chiffrées en BDD

### Bonnes pratiques pour le déploiement

```
✓ Activer HTTPS/SSL
✓ Définir APP_DEBUG=false en production
✓ Utiliser un .htaccess robuste
✓ Limiter les permissions des fichiers
✓ Configurer les headers de sécurité
✓ Mettre à jour PHP et les dépendances
✓ Sauvegarder régulièrement la base de données
✓ Monitorer les logs de sécurité
```

---

## 🗄️ Base de données

### Tables principales

#### `users`
Stocke les informations des utilisateurs du système.

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    role ENUM('admin', 'voter', 'candidate') NOT NULL DEFAULT 'voter',
    email_verified BOOLEAN DEFAULT FALSE,
    organization_id INT,
    is_active BOOLEAN DEFAULT TRUE,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### `elections`
Gère les élections et leurs paramètres.

```sql
CREATE TABLE elections (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('draft', 'active', 'closed', 'archived') DEFAULT 'draft',
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    organization_id INT,
    max_voters INT,
    created_by INT,
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### `candidates`
Liste des candidats à chaque élection.

```sql
CREATE TABLE candidates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    election_id INT NOT NULL,
    user_id INT,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    description TEXT,
    photo_url VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    vote_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE
);
```

#### `votes`
Enregistrement de tous les votes (anonymisé pour le scrutin).

```sql
CREATE TABLE votes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    election_id INT NOT NULL,
    voter_hash VARCHAR(255),  -- Hash anonyme du votant
    candidate_id INT NOT NULL,
    vote_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),  -- IPv4 ou IPv6
    browser_fingerprint VARCHAR(255),
    is_validated BOOLEAN DEFAULT TRUE,
    fraud_score INT DEFAULT 0,
    FOREIGN KEY (election_id) REFERENCES elections(id),
    FOREIGN KEY (candidate_id) REFERENCES candidates(id),
    INDEX idx_election_vote (election_id, vote_timestamp)
);
```

#### `audit_logs`
Traçabilité complète des actions administratives.

```sql
CREATE TABLE audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100),
    entity_type VARCHAR(50),
    entity_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_action (user_id, action),
    INDEX idx_created (created_at)
);
```

### Schémas personnalisés

Pour des détails sur d'autres tables (organizations, eligible_voters, etc.), consultez le schéma complet :

```bash
php bin/check_schema.php
```

---

## 🛠️ Développement

### Installation de l'environnement de développement

```bash
# Cloner et installer
git clone https://github.com/yourusername/vote-system.git
cd vote-system
composer install

# Copier la configuration
cp .env.example .env

# Créer la base de données
php bin/run_migration.php

# Créer un admin test
php bin/manage_admin.php --email admin@test.com --password admin123
```

### Scripts utiles

```bash
# Vérifier la configuration
php bin/test_autoload.php

# Vérifier le schéma de base de données
php bin/check_schema.php

# Ajouter une vérification aux utilisateurs
php bin/add_verification_to_users.php

# Démarrer le worker de file d'attente
php bin/queue_worker.php

# Test du système de queue
php bin/test_queue.php
```

### Conventions de codage

- **PSR-12** : Codage et style
- **PSR-4** : Autoloading des classes
- **Namespaces** : `App\ClassName`
- **Nommage** :
  - Classes : `PascalCase` (ex: `AdminController`)
  - Méthodes : `camelCase` (ex: `getUserById()`)
  - Constantes : `UPPERCASE` (ex: `MAX_LOGIN_ATTEMPTS`)
  - Variables : `camelCase` (ex: `$userId`)

### Variables POST/GET à valider

```php
// Toujours utiliser SecurityHelper pour l'entrée utilisateur
$email = SecurityHelper::sanitize($_POST['email'] ?? '');
$email = SecurityHelper::validateEmail($email);

$password = $_POST['password'] ?? '';
// Ne PAS sanitizer les mots de passe !
```

### Exemples de développement

#### Créer un nouveau contrôleur

```php
<?php
namespace App\Controllers;

class MonController {
    public function __construct() {
        // Vérifier l'authentification si nécessaire
        if (!isset($_SESSION['logged_in'])) {
            redirect('/auth/login');
        }
    }
    
    public function maFonction($param) {
        // Logique métier
        view('mon-template', ['data' => $data]);
    }
}
```

#### Créer un nouveau modèle

```php
<?php
namespace App\Models;

use App\Config\Database;

class MonModele {
    protected $table = 'ma_table';
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function findById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
```

#### Créer un service

```php
<?php
namespace App\Services;

class MonService {
    public function traiterData($data) {
        // Logique métier complexe
        return $resultat;
    }
}
```

---

## 📦 Déploiement

### Checklist de déploiement

```
☐ Cloner le repository en production
☐ Installer les dépendances avec composer install --optimize-autoloader --no-dev
☐ Configurer le fichier .env (DB, email, OAuth, reCAPTCHA)
☐ Créer les répertoires storage avec permissions correctes
☐ Configurer le serveur web (Apache/Nginx)
☐ Activer HTTPS/SSL
☐ Créer la base de données
☐ Exécuter les migrations : php bin/run_migration.php
☐ Créer l'admin initial : php bin/manage_admin.php
☐ Configurer les logs (rotation, sauvegardes)
☐ Mettre en place la sauvegarde de base de données
☐ Configurer les variables d'environnement critiques
☐ Tester les formulaires de contact/email
☐ Configurer le monitoring et les alertes
☐ Documenter les procédures de maintenance
```

### Déploiement rapide (Docker optionnel)

```dockerfile
FROM php:8.1-apache
RUN docker-php-ext-install pdo pdo_mysql mbstring
RUN a2enmod rewrite
COPY . /var/www/html
WORKDIR /var/www/html
RUN composer install --no-dev
```

```bash
docker build -t vote-system .
docker run -p 80:80 vote-system
```

### Mise à jour de l'application

```bash
cd /path/to/vote-system
git pull origin main
composer install --no-dev
php bin/run_migration.php
# Redémarrer les services si nécessaire
```

---

## 🐛 Dépannage

### Problèmes courants

#### Erreur : "Erreur de connexion à la base de données"

**Solution** :
- Vérifier que MySQL est démarré
- Vérifier les identifiants dans `.env`
- Vérifier les permissions de l'utilisateur MySQL

```bash
mysql -u root -p -e "SELECT 1;"
```

#### Erreur : "Permission denied" sur storage/

**Solution** :
```bash
chmod -R 777 storage/
chown -R www-data:www-data storage/
```

#### Page blanche à l'accès

**Solution** :
- Activer `APP_DEBUG=true` dans `.env`
- Vérifier les logs : `tail -f storage/logs/app.log`
- Vérifier que vendor/autoload.php existe
- Vérifier la configuration Apache (mod_rewrite activé)

#### Erreur "Token CSRF invalide"

**Solution** :
- Vérifier que les sessions PHP sont correctement configurées
- S'assurer que `session_start()` est appelé
- Vérifier que les cookies sont activés

#### Emails non envoyés

**Solution** :
- Vérifier la configuration SMTP dans `.env`
- Tester avec une adresse email valide
- Vérifier les logs : `storage/logs/app.log`
- Vérifier le firewall (port SMTP)

#### reCAPTCHA échoue

**Solution** :
- Vérifier les clés dans `.env` sont correctes
- Vérifier le domaine est autorisé dans Google Console
- Vérifier que l'application peut atteindre Google APIs

### Logs et debugging

```bash
# Afficher les logs en temps réel
tail -f storage/logs/app.log

# Vérifier les erreurs PHP
tail -f /var/log/apache2/error.log

# Vérifier les erreurs MySQL
tail -f /var/log/mysql/error.log

# Vérifier la configuration
php -r "phpinfo();"
```

---

## 📞 Support et Contribution

### Signaler un bug

Ouvrez une issue avec :
- Description du problème
- Étapes pour reproduire
- Version PHP/MySQL
- Navigateur utilisé
- Logs pertinents

### Contribuer

1. Fork le repository
2. Créer une branche (`git checkout -b feature/nom-feature`)
3. Commit vos changements (`git commit -m "Add feature"`)
4. Push vers la branche (`git push origin feature/nom-feature`)
5. Ouvrir une Pull Request

### Code de conduite

Soyez respectueux et constructif dans vos interactions.

---

## 📄 Licence

Ce projet est sous licence **MIT**. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

---

## 🙏 Remerciements

Merci à tous les contributeurs et à la communauté PHP !

---

## 📝 Changelog

### Version 1.0.0 (2026-06-28)
- ✅ Release initiale
- ✅ Authentification sécurisée
- ✅ Gestion complète des élections
- ✅ Système de vote sécurisé
- ✅ Dashboard administrateur
- ✅ Détection de fraude
- ✅ Export de rapports PDF
- ✅ Support multi-organisations

---

## 📧 Contact

Pour toute question ou suggestion :
- **Email** : support@votesystem.com
- **Issues** : https://github.com/yourusername/vote-system/issues
- **Documentation** : https://votesystem.com/docs

---

**Dernière mise à jour** : 28 juin 2026
**Mainteneur** : Votre Organisation
**Version** : 1.0.0
