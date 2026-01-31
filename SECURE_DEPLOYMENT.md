# Guide de déploiement sécurisé pour Aramis-Gestion

## Configuration de l'environnement

Pour déployer cette application Laravel en toute sécurité, suivez ces étapes :

### 1. Préparation du serveur
- Assurez-vous que votre serveur dispose de PHP 8.1+ et des extensions requises
- Installez Composer et NPM
- Configurez un pare-feu et des mises à jour système régulières

### 2. Clonez le dépôt
```bash
git clone https://github.com/votre-compte/aramis-gestion.git
cd aramis-gestion
```

### 3. Configuration de l'environnement
Copiez le fichier modèle et configurez vos variables d'environnement :
```bash
cp .env.save .env
```

Modifiez le fichier `.env` pour inclure vos valeurs spécifiques :
- Remplissez les informations de base de données
- Générez une nouvelle clé d'application : `php artisan key:generate`
- Configurez vos identifiants Shopify
- Configurez vos identifiants AWS si nécessaire

### 4. Installation des dépendances
```bash
composer install --optimize-autoloader --no-dev
npm install
npm run build
```

### 5. Configuration de la base de données
```bash
php artisan migrate --force
```

### 6. Permissions des répertoires
Assurez-vous que les répertoires suivants sont accessibles en écriture par le serveur web :
- `storage/`
- `bootstrap/cache/`

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
chmod -R 755 storage bootstrap/cache
```

### 7. Configuration du serveur web
Configurez votre serveur web (Apache/Nginx) pour pointer vers le sous-répertoire `public/` comme racine documentaire.

## Sécurité des variables d'environnement

Ne jamais commiter les fichiers `.env` contenant des informations sensibles. Le fichier `.env.save` fourni ne contient que des structures génériques.

Les variables sensibles incluent :
- APP_KEY (clé de cryptage)
- Identifiants de base de données
- Clés API (Shopify, AWS, etc.)
- Mots de passe de services tiers

## Protection contre les attaques courantes

Ce projet suit les meilleures pratiques de Laravel pour se protéger contre :
- Injection SQL (grâce à Eloquent ORM)
- XSS (protection CSRF intégrée)
- Attaques de type clickjacking
- Attaques DDoS basées sur la session

## Surveillance et journalisation

La journalisation est configurée selon les meilleures pratiques. Assurez-vous de surveiller régulièrement les fichiers de log situés dans `storage/logs/`.

## Révision finale avant déploiement

- [ ] Désactivez APP_DEBUG en production
- [ ] Vérifiez que tous les identifiants sont correctement configurés
- [ ] Testez la connexion à la base de données
- [ ] Vérifiez la configuration SSL/TLS
- [ ] Activez la surveillance de sécurité