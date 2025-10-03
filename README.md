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
# Benin Lodge — Guide d'installation, API et déploiement

Ce dépôt contient une application PHP légère (API REST) et un schéma de base de données. Le projet est conçu pour être exécuté en local via Docker (PHP + Apache et Microsoft SQL Server). Ce README décrit comment démarrer localement, comment déployer le frontend sur Vercel et le backend sur une plateforme Docker-friendly (ex. Render), ainsi que l'utilisation de l'API générique.

## Contenu du dépôt
- `index.html`, `css/`, `js/` : frontend statique
- `php/` : code PHP (controllers, models, scripts d'initialisation et API)
	- `php/config/database.php` : configuration de la connexion à MSSQL
	- `php/api/` : endpoints API (hotels, reservations, room-types, query.php)
	- `php/create_db.php` et `php/import_sql.php` : utilitaires d'initialisation
- `sql/database.sql` : schéma et données d'exemple
- `Dockerfile`, `docker-compose.yml` : configuration pour exécuter `web` (PHP) et `db` (MSSQL)

---

## Démarrage local (Docker)
1. Construire et lancer :

```bash
docker-compose up --build
```

2. Vérifier le service web :

Ouvrir : http://localhost:8080/php/test.php

3. Créer la base et importer les données (si nécessaire) :

```bash
docker exec benin-lodge-web-1 php /var/www/html/php/create_db.php
docker exec benin-lodge-web-1 php /var/www/html/php/import_sql.php
```

---

## Configuration / Variables d'environnement
Utiliser des variables d'environnement plutôt que de stocker des credentials en clair.

Variables conseillées :

```
DB_SERVER=host,port
DB_NAME=benin_lodge_db
DB_USER=SA
DB_PASS=your_password
API_TOKEN=un_token_long_et_secret
```

Modifiez `php/config/database.php` pour lire ces variables (getenv) si nécessaire.

---

## API — endpoint générique sécurisé
Un endpoint générique est disponible : `php/api/query.php`.

Actions supportées (GET) :
- `action=list_hotels`
- `action=get_hotel&id=ID`
- `action=list_room_types`
- `action=list_reservations`

Authentification :
- Le endpoint vérifie `API_TOKEN` si défini côté serveur.
- Envoyer le token via l'en-tête `X-API-KEY: <token>` ou en paramètre `api_key`.

Exemples :

```bash
# Lister les hôtels
curl "https://TON_BACKEND/php/api/query.php?action=list_hotels"

# Avec token
curl -H "X-API-KEY: TON_TOKEN" "https://TON_BACKEND/php/api/query.php?action=list_hotels"

# Obtenir un hôtel
curl "https://TON_BACKEND/php/api/query.php?action=get_hotel&id=1&api_key=TON_TOKEN"
```

Exemple front-end (fetch) :

```js
fetch('https://TON_BACKEND/php/api/query.php?action=list_hotels', { headers: { 'X-API-KEY': 'TON_TOKEN' } })
	.then(r => r.json())
	.then(data => console.log(data));
```

---

## Déploiement recommandé
- Frontend (statique) → Vercel (déployer la racine du repo contenant `index.html`)
- Backend (PHP Docker) → Render / DigitalOcean App Platform / autre plateforme qui supporte Docker
- Base de données → Azure SQL (ou un service managé) recommandé pour MSSQL.

Workflow :
1. Préparer le repo : ajouter `.env.example` et `.gitignore` (ne pas committer `.env`).
2. Déployer le backend sur Render (Docker) et définir les variables d'environnement (`DB_SERVER`, `DB_NAME`, `DB_USER`, `DB_PASS`, `API_TOKEN`).
3. Déployer le frontend sur Vercel et définir `API_URL` vers l'URL publique du backend.
4. Importer le schéma via `php/import_sql.php` ou `sqlcmd`.

---

## Sécurité & bonnes pratiques
- Ne commitez jamais de secrets ; utilisez des variables d'environnement.
- Changez le mot de passe SA si il a été poussé par erreur et retirez-le du dépôt (git rm --cached).
- Utilisez TLS pour les connexions à la base en production.
- Restreignez l'accès réseau au serveur MSSQL.

---

## Commandes utiles

```bash
docker-compose up --build
docker-compose up -d --build
docker-compose logs -f web
docker-compose logs -f db
docker exec benin-lodge-web-1 php /var/www/html/php/import_sql.php
docker exec benin-lodge-web-1 php /var/www/html/php/create_db.php
```

---

Si tu veux, je peux :
- ajouter `.env.example` et `.gitignore` et mettre à jour `php/config/database.php` pour lire les variables d'environnement,
- ajouter un petit script d'admin pour lancer l'import depuis l'interface (protégé par token),
- préparer un guide pas-à-pas pour Render + Azure SQL et exécuter les changements nécessaires.

Dis-moi quelle action tu veux que je fasse ensuite.
## Importer `sql/database.sql`
