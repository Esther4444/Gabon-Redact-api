# Système d'Authentification - RedacGabonPro API

## Vue d'ensemble

Le système d'authentification a été configuré pour permettre la connexion uniquement (inscription désactivée) avec trois rôles spécifiques :

- **Directeur de Publication** (`directeur_publication`)
- **Secrétaire de Rédaction** (`secretaire_redaction`) 
- **Journaliste** (`journaliste`)

## Utilisateurs de Test

### Directeur de Publication
- **Email** : `directeur@redacgabon.com`
- **Mot de passe** : `password123`
- **Rôle** : `directeur_publication`

### Secrétaire de Rédaction
- **Email** : `secretaire@redacgabon.com`
- **Mot de passe** : `password123`
- **Rôle** : `secretaire_redaction`

### Journalistes
- **Email** : `journaliste@redacgabon.com`
- **Mot de passe** : `password123`
- **Rôle** : `journaliste`

- **Email** : `journaliste2@redacgabon.com`
- **Mot de passe** : `password123`
- **Rôle** : `journaliste`

- **Email** : `journaliste3@redacgabon.com`
- **Mot de passe** : `password123`
- **Rôle** : `journaliste`

## Endpoints API

### 1. Connexion
```http
POST /api/login
Content-Type: application/json

{
    "email": "directeur@redacgabon.com",
    "password": "password123",
    "role": "directeur_publication"
}
```

**Paramètres requis :**
- `email` : Adresse email de l'utilisateur
- `password` : Mot de passe
- `role` : Rôle de l'utilisateur (`journaliste`, `directeur_publication`, `secretaire_redaction`)

**Réponse de succès :**
```json
{
    "success": true,
    "data": {
        "token": "2|vcS59kDjcRVVbcoX6kBRznnVICPk8AxDzXSwdqPg998be1e0",
        "user": {
            "id": 1,
            "name": "Directeur de Publication",
            "email": "directeur@redacgabon.com",
            "role": "directeur_publication",
            "full_name": "Directeur de Publication",
            "avatar_url": "https://ui-avatars.com/api/?name=Directeur+de+Publication&background=random"
        }
    }
}
```

**Réponse d'erreur (mauvais rôle) :**
```json
{
    "success": false,
    "message": "Le rôle sélectionné ne correspond pas à votre compte.",
    "errors": {
        "role": ["Le rôle sélectionné ne correspond pas à votre compte."]
    }
}
```

**Réponse d'erreur (identifiants invalides) :**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["Les identifiants sont invalides."]
    }
}
```

### 2. Déconnexion
```http
POST /api/logout
Authorization: Bearer {token}
```

### 3. Lister les utilisateurs disponibles
```http
GET /api/auth/users
```

**Réponse :**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Directeur de Publication",
            "email": "directeur@redacgabon.com",
            "role": "directeur_publication",
            "full_name": "Directeur de Publication"
        },
        // ... autres utilisateurs
    ]
}
```

### 4. Profil utilisateur
```http
GET /api/user/profile
Authorization: Bearer {token}
```

## Utilisation

1. **Connexion** : Utilisez l'endpoint `/api/login` avec les identifiants fournis
2. **Token** : Le token reçu doit être inclus dans l'en-tête `Authorization: Bearer {token}` pour les requêtes authentifiées
3. **Rôles** : Chaque utilisateur a un rôle spécifique qui peut être utilisé pour contrôler l'accès aux fonctionnalités

## Notes

- L'inscription est désactivée - seuls les utilisateurs pré-configurés peuvent se connecter
- Tous les utilisateurs ont le même mot de passe par défaut : `password123`
- Les rôles sont stockés dans la table `profiles` et liés aux utilisateurs
- Le système utilise Laravel Sanctum pour la gestion des tokens d'authentification
