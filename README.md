# OMpaye - API de Paiement Mobile

## Description
OMpaye est une API REST Laravel pour un système de paiement mobile permettant aux utilisateurs de gérer leurs comptes, effectuer des transactions et consulter leurs soldes.

## Installation

### Prérequis
- PHP 8.1+
- Composer
- PostgreSQL
- Node.js & npm (pour les assets frontend)

### Configuration
1. Cloner le repository
```bash
git clone <repository-url>
cd ompaye
```

2. Installer les dépendances PHP
```bash
composer install
```

3. Copier le fichier d'environnement
```bash
cp .env.example .env
```

4. Configurer la base de données dans `.env`
```env
DB_CONNECTION=pgsql
DB_HOST=your_host
DB_PORT=5432
DB_DATABASE=ompaye
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. Générer la clé d'application
```bash
php artisan key:generate
```

6. Exécuter les migrations
```bash
php artisan migrate
```

7. Alimenter la base de données
```bash
php artisan db:seed
```

8. Installer les dépendances frontend (optionnel)
```bash
npm install
npm run build
```

## Démarrage du serveur
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

L'API sera accessible sur `http://localhost:8000`

## Documentation API

### Authentification
L'API utilise OAuth2 avec Passport pour l'authentification. Tous les endpoints nécessitent un token Bearer.

#### Flux d'authentification :
1. **Inscription** : `POST /api/v1/auth/register`
2. **Vérification OTP** : `POST /api/v1/auth/verify`
3. **Connexion** : `POST /api/v1/auth/login`
4. **Vérification OTP de connexion** : `POST /api/v1/auth/verify-login`

### Endpoints Principaux

#### Comptes Utilisateur

##### Récupérer les informations d'un compte
```http
GET /api/v1/accounts/{numerocompte}
Authorization: Bearer {token}
```

**Réponse :**
```json
{
  "numerocompte": "KC-00001234",
  "telephone": "+22177xxxxxxx",
  "solde": 12500,
  "qrCode": "data:image/png;base64,...",
  "transactions_summary": {
    "count": 12,
    "last": "2025-11-10T14:22:00Z"
  }
}
```

##### Récupérer le solde d'un compte
```http
GET /api/v1/accounts/{numerocompte}/balance
Authorization: Bearer {token}
```

**Réponse :**
```json
{
  "numerocompte": "KC-00001234",
  "solde": 12500
}
```

##### Récupérer les transactions d'un compte
```http
GET /api/v1/accounts/{numerocompte}/transactions?limit=20&page=1
Authorization: Bearer {token}
```

#### Transactions

##### Dépôt
```http
POST /api/v1/transactions/depot
Authorization: Bearer {token}
Content-Type: application/json

{
  "montant": 5000,
  "description": "Dépôt mobile"
}
```

##### Retrait
```http
POST /api/v1/transactions/retrait
Authorization: Bearer {token}
Content-Type: application/json

{
  "montant": 2000,
  "description": "Retrait DAB"
}
```

##### Transfert
```http
POST /api/v1/transactions/transfert
Authorization: Bearer {token}
Content-Type: application/json

{
  "montant": 1000,
  "compte_destinataire": "KC-00005678",
  "description": "Transfert familial"
}
```

##### Paiement marchand
```http
POST /api/v1/transactions/paiement
Authorization: Bearer {token}
Content-Type: application/json

{
  "montant": 500,
  "code_marchand": "OMN001",
  "description": "Paiement Orange Money"
}
```

## Données de Test

### Comptes Utilisateur
- `733471982` (Solde: 0)
- `602613957` (Solde: 0)
- `876402743` (Solde: 0)
- `KC-42709747` (Solde: 0)

### Codes Marchands
- `OMN001` - Orange Money
- `9AZEWP` - Dare-Wuckert
- `XB5KLC` - Torp, Bogan and Doyle

## Codes d'Erreur

### Authentification
- `401 Unauthorized` : Token manquant ou invalide

### Comptes
- `404 Not Found` : Compte non trouvé
- `400 Bad Request` : Format de numéro de compte invalide
- `500 Internal Server Error` : Erreur interne du serveur

### Transactions
- `400 Bad Request` : Données invalides
- `422 Unprocessable Entity` : Solde insuffisant
- `500 Internal Server Error` : Erreur de traitement

## Technologies Utilisées
- **Backend** : Laravel 10, PHP 8.1+
- **Base de données** : PostgreSQL
- **Authentification** : Laravel Passport (OAuth2)
- **Documentation** : Laravel Swagger/OpenAPI
- **QR Codes** : SimpleSoftwareIO QR Code
- **SMS** : Twilio
- **Email** : Mailjet

## Développement

### Génération de la documentation API
```bash
php artisan l5-swagger:generate
```

### Tests
```bash
php artisan test
```

### Migration de base de données
```bash
php artisan migrate:fresh --seed
```

## Support
Pour toute question ou problème, veuillez contacter l'équipe de développement.

## Licence
Ce projet est sous licence MIT.
