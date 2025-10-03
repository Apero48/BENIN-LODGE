# Déployer le backend (PHP + SQL Server)

Ce document explique plusieurs façons simples de déployer le backend de BENIN LODGE (le code PHP situé dans `php/` et l'image Docker définie dans `Dockerfile` et `docker-compose.yml`). Il couvre : déploiement sur un VPS avec Docker Compose, et options pour Render / Railway / Fly.io.

Important : GitHub Pages ne peut pas exécuter PHP. Le frontend statique peut rester sur GitHub Pages et appeler le backend via `window.API_BASE_URL`.

---

## 1) Déploiement recommandé : VPS (Docker + Docker Compose)

Prérequis sur le VPS :
- Docker installé
- Docker Compose (v2 ou v1 compatible)
- Un nom de domaine ou une IP publique

Étapes rapides :

1. Copier le dépôt sur le VPS (git clone) ou pousser un tarball.

2. Placer un fichier `.env` dans le répertoire du projet (même dossier que `docker-compose.yml`). Exemple minimal :

```env
# .env
DB_SERVER=0.0.0.0
DB_NAME=benin_lodge_db
DB_USER=SA
DB_PASS=YourStrong!Passw0rd
API_TOKEN=changeme
PORT_HTTP=80
PORT_MSSQL=1433
```

3. Ajuster `docker-compose.yml` si besoin (ports, volumes). Puis lancer :

```bash
docker compose up -d --build
```

4. Vérifier les logs :

```bash
docker compose logs -f web
docker compose logs -f db
```

5. S'assurer que le conteneur web est accessible depuis l'URL publique (HTTP/80). Si tu utilises un nom de domaine, configure un reverse proxy (nginx) ou mappe le port 80 directement.

6. CORS : sur le backend PHP, autorise uniquement ton frontend en production en remplaçant `*` :

```php
header("Access-Control-Allow-Origin: https://<ton-front>.github.io");
```

---

## 2) Déployer sur Render (service web + base de données)

Render peut exécuter une application Docker.

- Crée un nouveau service Web -> Advanced -> Docker. Pousse la branche et déclenche un déploiement depuis ton repo.
- Configure les secrets (DB_USER, DB_PASS, API_TOKEN) via l'UI Render.
- Si tu veux MSSQL, Render ne propose pas directement une image MSSQL managée ; tu peux utiliser une base distante (Azure SQL / autre) ou déployer MSSQL dans un service Docker (mais attention aux limitations et coûts). Pour la simplicité, considère utiliser une base SQL Server managée (Azure) ou remplacer temporairement par SQLite/MySQL selon tes besoins.

---

## 3) Déployer sur Railway / Fly.io

Ces plateformes acceptent les images Docker. Procédé général :
- Crée un nouveau service, connecte ton repo GitHub, choisis Dockerfile.
- Ajoute les variables d'environnement (DB_*, API_TOKEN).
- Si la plateforme offre une base SQL managée, préfère-la pour la production.

---

## 4) Exemple de configuration `.env.example`

```env
DB_SERVER=db
DB_NAME=benin_lodge_db
DB_USER=SA
DB_PASS=YourStrong!Passw0rd
API_TOKEN=change_this_token
```

Assure-toi de ne jamais pousser de vrais secrets dans le repo public.

---

## 5) Points CORS et sécurité

- Pour le développement, `Access-Control-Allow-Origin: *` est acceptable.
- En production, définis précisément l'origine autorisée.
- Ajoute une vérification d'`API_TOKEN` côté serveur pour protéger les endpoints publics.
- Si tu exposes MSSQL sur Internet, verrouille l'accès (firewall, réseau privé). Idéalement, la DB n'est accessible que depuis le réseau interne du serveur qui héberge l'app.

---

## 6) Vérification post-déploiement

1. Accède à `https://<ton-backend>/php/test.php` (ou `http://` si non HTTPS) pour vérifier la connexion DB.
2. Teste `https://<ton-backend>/php/api/query.php?action=list_reservations` depuis ton navigateur (ou curl) — tu dois obtenir JSON.
3. Configure `js/config.js` côté frontend (sur GitHub Pages) :

```js
// js/config.js
window.API_BASE_URL = 'https://<ton-backend>';
```

4. Ouvre la console du navigateur si ça ne marche pas, regarde les erreurs réseau (404, 500, CORS).

---

## 7) Assistance personnalisée
Si tu veux, je peux :
- Te préparer un `server-deploy.sh` (script d'installation sur un VPS Debian/Ubuntu) qui installe Docker, clone le repo, place `.env` et lance `docker compose up -d`.
- T'aider à déployer sur une plateforme précise (Render, Railway, Fly.io). Donne-moi la plateforme choisie et j'écris la procédure exacte.

---

Fin du guide.
