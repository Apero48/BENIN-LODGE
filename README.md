# Benin Lodge - Docker setup pour PHP + SQL Server

Ce dépôt contient une configuration Docker minimale pour exécuter l'application PHP avec une base de données Microsoft SQL Server (MSSQL).

Prérequis
- Docker et Docker Compose installés sur votre machine.

Builder et lancer
1. Construire et lancer les services :

```bash
docker-compose up --build
# Benin Lodge — Guide d'installation et FAQ

Ce dépôt contient une application PHP simple (back-office minimal + scripts) et une base de données Microsoft SQL Server (MSSQL). Le projet est configuré pour être exécuté en local avec Docker afin d'éviter l'installation manuelle des extensions SQL Server sur macOS.

## Contenu du dépôt (aperçu)
- `index.html`, `css/`, `js/` : frontend/ressources statiques (interface publique/dashboard)
- `php/` : code PHP, configuration et scripts utilitaires
	- `php/config/database.php` : helper de connexion à SQL Server (PDO + fallback sqlsrv)
	- `php/test.php` : endpoint de test (SELECT GETDATE())
	- `php/create_db.php` : script utilisé pour créer la base `benin_lodge_db` (exécuté durant notre session)
	- `php/import_sql.php` : script d'import des tables et données de `sql/database.sql`
- `sql/database.sql` : schéma et données d'exemple (hotels, types_chambres, reservations)
- `Dockerfile` et `docker-compose.yml` : configuration Docker pour `web` (PHP+Apache) et `db` (MSSQL Server)

## Architecture et principe
- Service `web` : PHP 8.2 + Apache, contient l'application et les scripts PHP. Les extensions `pdo_sqlsrv` et `sqlsrv` sont installées dans l'image.
- Service `db` : Microsoft SQL Server (image officielle). La base `benin_lodge_db` est créée et initialisée via un script PHP exécuté depuis le service `web`.
- Réseau Docker interne : les services communiquent via le nom de service `db` (ex. `db:1433`). Le port hôte exposé par défaut est `8080` pour le web et `1433` pour MSSQL (modifiable si conflit).

## Prérequis
- Docker et Docker Compose installés.
- (Optionnel) Sur macOS, Docker Desktop doit être configuré pour supporter les images linux/amd64 (le `docker-compose.yml` force `platform: linux/amd64` si nécessaire).

## Démarrage rapide (local)
1. Construire et lancer les services :

```bash
docker-compose up --build
```

2. Vérifier le service web :

Ouvrir dans le navigateur :

http://localhost:8080/php/test.php

Ce script exécute `SELECT GETDATE()` sur MSSQL pour vérifier la connexion.

3. (Si nécessaire) Créer la base et importer le schéma/données :

- Créer la base (déjà exécuté dans cette session) :

```bash
docker exec benin-lodge-web-1 php /var/www/html/php/create_db.php
```

- Importer le schéma et les données :

```bash
docker exec benin-lodge-web-1 php /var/www/html/php/import_sql.php
```

Après import, les tables `hotels`, `types_chambres` et `reservations` sont présentes dans `benin_lodge_db`.

## Variables et configuration
- `docker-compose.yml` contient les variables principales pour MSSQL :
	- `SA_PASSWORD` : mot de passe pour l'utilisateur `SA` (par défaut `YourStrong!Passw0rd` dans l'exemple). Changez-le en production.
- `php/config/database.php` contient les paramètres de connexion (`server`, `db_name`, `username`, `password`). En environnement réel, il est recommandé d'utiliser des variables d'environnement ou un fichier `.env` pour ne pas stocker de secrets dans le dépôt.

Exemple de lecture depuis les variables d'environnement (recommandé) :

```php
$server = getenv('DB_SERVER') ?: 'db,1433';
$dbName = getenv('DB_NAME') ?: 'benin_lodge_db';
$user = getenv('DB_USER') ?: 'SA';
$pass = getenv('DB_PASS') ?: 'YourStrong!Passw0rd';
```

## Importer `sql/database.sql`
- Le fichier `sql/database.sql` fourni dans le dépôt contient un schéma MySQL-like (AUTO_INCREMENT, ENUM). Pour MSSQL, le script d'import (`php/import_sql.php`) crée des tables compatibles T-SQL et insère les données de test.
- Si vous préférez importer directement via `sqlcmd` :
	- Installer `mssql-tools` dans votre environnement ou dans le container `db`.
	- Exécuter :

```bash
docker exec -it benin-lodge-db-1 /opt/mssql-tools/bin/sqlcmd -S localhost -U SA -P 'YourStrong!Passw0rd' -d benin_lodge_db -i /path/to/sql/database.sql
```

Note : notre import PHP évite la nécessité d'installer `sqlcmd`.

## Vérifications et debugging
- Voir les logs Docker :

```bash
docker-compose logs -f web
docker-compose logs -f db
```

- Lister les containers :

```bash
docker ps
```

- Si vous avez un conflit de port (ex. 1433 déjà utilisé), changez l'exposition dans `docker-compose.yml` (ex. `11433:1433`) ou arrêtez le container qui occupe le port.

## Sécurité et bonnes pratiques
- Ne stockez pas de secrets (mot de passe SA) dans le dépôt. Utilisez un fichier `.env` ou des variables d'environnement fournies au runtime.
- Ne laissez pas exposé le port MSSQL (1433) sur un réseau public ; limitez l'accès au réseau Docker ou utilisez un VPN.
- Changez le mot de passe `SA` par une valeur forte et unique.

## FAQ (réponses prêtes)

- Q : Quelle version de PHP et MSSQL ?
	- R : PHP 8.2 (image `php:8.2-apache-bullseye`), MSSQL 2019 (image `mcr.microsoft.com/mssql/server:2019-latest`).

- Q : Où sont les scripts d'initialisation de la base ?
	- R : `sql/database.sql` contient le schéma d'origine. Pour MSSQL, utilisez `php/import_sql.php` (déjà exécuté) ou `sqlcmd`.

- Q : Comment puis-je me connecter à la base depuis un outil externe (SSMS) ?
	- R : Hôte : `localhost`, Port : `1433` (ou le port hôte que vous avez choisi), Utilisateur : `SA`, Mot de passe : celui défini dans `docker-compose.yml`.

- Q : L'application fonctionne-t-elle sur macOS ?
	- R : Oui — le projet fonctionne via Docker. Docker Desktop doit permettre l'exécution d'images linux/amd64 (la configuration `platform: linux/amd64` est déjà présente si nécessaire).

- Q : Le projet est prêt pour la production ?
	- R : Non — c'est une configuration de développement. Avant la production il faut : sécuriser les secrets, configurer TLS, limiter l'exposition des ports, ajouter des sauvegardes, et revoir la configuration des extensions et performances.

## Commandes utiles (récapitulatif)

```bash
# Builder et démarrer
docker-compose up --build

# Démarrer en arrière-plan (detached)
docker-compose up -d --build

# Voir les logs
docker-compose logs -f web
docker-compose logs -f db

# Exécuter un script PHP dans le container web
docker exec benin-lodge-web-1 php /var/www/html/php/import_sql.php

# Créer la base (si besoin)
docker exec benin-lodge-web-1 php /var/www/html/php/create_db.php

# Se connecter au container DB (sqlcmd si installé)
docker exec -it benin-lodge-db-1 /opt/mssql-tools/bin/sqlcmd -S localhost -U SA -P 'YourStrong!Passw0rd'
```

---

Si tu veux, je peux :
- committer les scripts `php/create_db.php` et `php/import_sql.php` dans le dépôt,
- ajouter un `.env.example` et modifier `php/config/database.php` pour lire les variables d'environnement,
- ajouter une route d'administration pour visualiser les tables depuis le navigateur.

Dis-moi quelle option tu veux que j'implémente ensuite.
# BENIN-LODGE
