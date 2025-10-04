# Guide d'intégration Frontend - RedacGabonPro API

## Configuration de l'API

Votre frontend React est maintenant compatible avec l'API mise à jour. Voici les modifications nécessaires :

### 1. Mise à jour du service API

Modifiez votre `apiService` pour inclure le paramètre `role` dans la requête de connexion :

```typescript
// Dans votre apiService
const login = async (credentials: { email: string; password: string; role: string }) => {
  const response = await fetch(`${API_BASE_URL}/api/login`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(credentials),
  });

  if (!response.ok) {
    const errorData = await response.json();
    throw new Error(errorData.message || 'Erreur de connexion');
  }

  return response.json();
};
```

### 2. Gestion des erreurs

L'API retourne maintenant des erreurs spécifiques pour les rôles incorrects :

```typescript
// Exemple de gestion d'erreur dans votre composant
try {
  const result = await apiService.login({
    email: formData.email,
    password: formData.password,
    role: formData.role
  });
  
  // Connexion réussie
  console.log('Connexion réussie:', result);
  
} catch (error: any) {
  // Gestion des erreurs spécifiques
  if (error.message.includes('rôle sélectionné ne correspond pas')) {
    setError('Le rôle sélectionné ne correspond pas à votre compte.');
  } else if (error.message.includes('identifiants sont invalides')) {
    setError('Email ou mot de passe incorrect.');
  } else {
    setError('Une erreur est survenue lors de la connexion.');
  }
}
```

### 3. Structure de réponse

La réponse de connexion inclut maintenant toutes les informations utilisateur :

```typescript
interface LoginResponse {
  success: boolean;
  data: {
    token: string;
    user: {
      id: number;
      name: string;
      email: string;
      role: string;
      full_name: string;
      avatar_url: string | null;
    };
  };
}
```

### 4. Rôles disponibles

Les rôles valides dans votre frontend correspondent exactement à ceux de l'API :

- `journaliste` - Journaliste
- `directeur_publication` - Directeur de publication  
- `secretaire_redaction` - Secrétaire de rédaction

### 5. Utilisateurs de test

| Rôle | Email | Mot de passe | Rôle API |
|------|-------|--------------|----------|
| Directeur de Publication | `directeur@redacgabon.com` | `password123` | `directeur_publication` |
| Secrétaire de Rédaction | `secretaire@redacgabon.com` | `password123` | `secretaire_redaction` |
| Journaliste Principal | `journaliste@redacgabon.com` | `password123` | `journaliste` |
| Journaliste Senior | `journaliste2@redacgabon.com` | `password123` | `journaliste` |
| Journaliste Junior | `journaliste3@redacgabon.com` | `password123` | `journaliste` |

### 6. Validation côté frontend

Votre composant React est déjà bien configuré ! Le champ `role` est correctement inclus dans le formulaire et sera envoyé à l'API.

### 7. Stockage du token

Après une connexion réussie, stockez le token pour les requêtes authentifiées :

```typescript
// Stockage du token
localStorage.setItem('authToken', result.data.token);
localStorage.setItem('user', JSON.stringify(result.data.user));

// Utilisation du token dans les requêtes
const token = localStorage.getItem('authToken');
const response = await fetch(`${API_BASE_URL}/api/user/profile`, {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
  },
});
```

## Test de l'intégration

Votre frontend devrait maintenant fonctionner parfaitement avec l'API. Testez avec les différents utilisateurs et rôles pour vérifier que :

1. ✅ La connexion fonctionne avec le bon rôle
2. ✅ Une erreur est affichée avec un mauvais rôle
3. ✅ Les informations utilisateur sont correctement récupérées
4. ✅ Le token est généré et peut être utilisé pour les requêtes authentifiées

## Notes importantes

- L'inscription est désactivée - seuls les utilisateurs pré-configurés peuvent se connecter
- Le rôle doit correspondre exactement à celui configuré dans la base de données
- Tous les utilisateurs ont le même mot de passe par défaut : `password123`
- L'API valide à la fois les identifiants ET le rôle sélectionné
