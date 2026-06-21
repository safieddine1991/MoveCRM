# MoveCRM — Installation locale (développement)

Guide reproductible pour faire tourner MoveCRM en local. MoveCRM est un dérivé
d'EspoCRM (AGPL-3.0) ; la base technique suit la doc EspoCRM.

## Prérequis

- PHP 8.3–8.5 (extensions : pdo_mysql, mbstring, gd, zip, openssl, curl, exif…)
- Composer 2
- MySQL 8 / MariaDB 10.4+
- Node.js ≥ 20 + npm ≥ 8 (uniquement pour builder le frontend)

## 1. Dépendances backend

```bash
composer install
```

## 2. Base de données

```sql
CREATE DATABASE movecrm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'movecrm'@'localhost' IDENTIFIED BY 'VOTRE_MDP';
GRANT ALL PRIVILEGES ON movecrm.* TO 'movecrm'@'localhost';
FLUSH PRIVILEGES;
```

Copier `.env.example` vers `.env` et renseigner les identifiants.

## 3. Installation EspoCRM (CLI)

```bash
set -a; . ./.env; set +a
DB="host-name=${DB_HOST}&db-name=${DB_NAME}&db-user-name=${DB_USER}&db-user-password=${DB_PASSWORD}&db-platform=Mysql"

php install/cli.php -a checkPermission
php install/cli.php -a settingsTest      -d "$DB"
php install/cli.php -a setupConfirmation -d "$DB"
php install/cli.php -a saveSettings      -d "${DB}&user-lang=fr_FR&site-url=${SITE_URL}&default-permissions-user=$(id -un)&default-permissions-group=$(id -gn)"
```

Construire le schéma (crée toutes les tables, y compris les entités immobilier) :

```bash
# activer l'app puis construire le schéma depuis les métadonnées
php command.php rebuild
```

Créer l'administrateur :

```bash
php install/cli.php -a createUser -d "user-name=${ADMIN_USER}&user-pass=${ADMIN_PASSWORD}"
```

> Note : sous PHP 8.5, le moteur de templates Smarty de l'installeur émet des
> avertissements `Deprecated` sans incidence sur le résultat.

## 4. Frontend

```bash
npm install
npm run build-dev   # ou: npm run build  (build de production)
```

## 5. Lancer

```bash
php -S localhost:8080 -t public public/index.php
```

Ouvrir http://localhost:8080 et se connecter avec l'admin créé.

## Couche métier immobilier

Les entités immobilier (Bien, Mandat, Propriétaire, Acquéreur, Visite) sont
définies en métadonnées versionnées dans
`custom/Espo/Custom/Resources/`. Après toute modification, relancer
`php command.php rebuild` pour répercuter en base.
