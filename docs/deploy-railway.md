# Déployer le backend sur Railway

Ce guide explique comment déployer le backend PHP de BENIN LODGE sur Railway.

Remarque importante : Railway propose des services conteneurisés mais ne fournit pas nativement une instance Microsoft SQL Server managée gratuite. Pour la base de données vous avez deux choix :

A) Héberger la base SQL Server ailleurs (Azure SQL, instance gérée) et fournir les credentials à Railway.
B) Héberger MSSQL dans un service Railway (service Docker) — possible mais attention au stockage persistant et aux limitations.

---

Pré-requis :
- Un compte Railway (tu en as déjà un).
- Repo GitHub connecté (autorise Railway à accéder au repo `Apero48/BENIN-LODGE`).

## 1) Connecter ton repo à Railway
1. Sur Railway, crée un nouveau projet -> "Deploy from GitHub" et sélectionne ton repo.
2. Choisis la branche `RAasani` (ou `main` si tu préfères).

Railway détecte souvent automatiquement des services. Pour nous, on veut déployer le service web PHP via le `Dockerfile` présent dans le repo.

## 2) Déployer en utilisant le Dockerfile (méthode recommandée)
Railway peut construire l'image à partir de ton Dockerfile.

1. Dans le dashboard du projet Railway, fais "New Service" -> "Deploy from GitHub" -> sélectionne ton repo/branche et indique le chemin (racine).
2. Si Railway propose plusieurs services détectés, choisit le service basé sur Dockerfile.
3. Dans la configuration du service, ajoute les variables d'environnement nécessaires (voir section Variables).
4. Lance le déploiement. Railway construira l'image et exposera le service.

Variables d'environnement à ajouter (Railway -> Settings -> Variables) :
- DB_SERVER (ip/host de la DB; si DB gérée: l'hostname fourni)
- DB_NAME (benin_lodge_db)
- DB_USER
- DB_PASS
- API_TOKEN (une clé secrète pour protéger l'API)
- (optionnel) PORT_HTTP (si besoin)

## 3) DB : options pratiques
- Option A (recommandée pour production) : Utiliser Azure SQL ou un autre service SQL Server managé. Tu crées la base, notes l'host, port, user, pass, et tu les ajoutes comme variables d'environnement dans Railway.
- Option B (faire tourner MSSQL dans Railway) :
  - Crée un second service Railway basé sur la même image MSSQL (par ex. `mcr.microsoft.com/mssql/server:2019-latest`) via Docker.
  - Assure-toi d'ajouter un volume persistant (Railway a ses propres façons de gérer persistance — attention aux limitations gratuites).
  - Configure `DB_SERVER` avec le nom/host interne fourni par Railway (ou l'IP privée si disponible).

Note : la méthode A évite les soucis de limitations et sauvegarde.

## 4) CORS
- Avant toute chose, assure-toi que ton backend retourne les headers CORS corrects. Dans les fichiers API PHP tu as déjà :

```php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
```

- En production remplace `*` par l'origine exacte de ton frontend (ex: `https://<username>.github.io` ou `https://app.monsite.com`).

## 5) Après déploiement
1. Railway fournit une URL publique pour le service (ex: https://<generated>.up.railway.app).
2. Visite `https://<generated>.up.railway.app/php/test.php` pour vérifier la connexion DB.
3. Visite `https://<generated>.up.railway.app/php/api/query.php?action=list_reservations` pour vérifier la réponse JSON.
4. Ajoute dans `js/config.js` (frontend) :

```js
window.API_BASE_URL = 'https://<generated>.up.railway.app';
```

5. Recharger le frontend (GitHub Pages) et vérifier dans la console que les requêtes aboutissent.

## 6) Variables à définir dans Railway (récapitulatif)
- DB_SERVER
- DB_NAME
- DB_USER
- DB_PASS
- API_TOKEN

## 7) Si tu veux, je peux faire ça pour toi
Je peux :
- Te guider pas-à-pas via l'interface Railway en direct (tu me donnes les accès ou tu suis mes instructions).
- Ou générer un ensemble de steps à coller (captures/texte) et vérifier ta configuration après que tu aies créé le service.

Dis-moi si tu veux que je :
A) Te guide pas-à-pas et vérifie l'URL publique une fois déployée (je pourrai alors tester les endpoints et t'aider sur CORS) ; ou
B) Génère un script d'instructions détaillées (avec screenshots textuels) que tu peux suivre tout seul.
