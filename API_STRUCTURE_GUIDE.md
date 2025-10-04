# Guide de Structure API - RedacGabonPro

## Problème résolu : `folders.map is not a function`

Le problème était que l'API retournait des données paginées au lieu de tableaux simples, ce qui causait l'erreur `folders.map is not a function` dans votre frontend React.

## Structure des réponses API

Tous les endpoints de liste retournent maintenant la même structure :

```json
{
  "success": true,
  "data": [
    // Tableau d'objets directement
  ]
}
```

## Endpoints corrigés

### 1. Dossiers
```http
GET /api/folders
Authorization: Bearer {token}
```

**Réponse :**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "owner_id": 1,
      "name": "Actualités Politiques",
      "created_at": "2025-09-29T10:58:59.000000Z",
      "updated_at": "2025-09-29T10:58:59.000000Z"
    },
    // ... autres dossiers
  ]
}
```

### 2. Articles
```http
GET /api/articles
Authorization: Bearer {token}
```

**Réponse :**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Titre de l'article",
      "slug": "titre-de-larticle",
      "content": "Contenu...",
      "status": "draft",
      "folder_id": 1,
      "created_by": 1,
      "assigned_to": null,
      "seo_title": "SEO Title",
      "seo_description": "Description SEO",
      "seo_keywords": ["mot1", "mot2"],
      "published_at": null,
      "metadata": {...},
      "created_at": "2025-09-29T10:58:59.000000Z",
      "updated_at": "2025-09-29T10:58:59.000000Z",
      "creator": {
        "id": 1,
        "name": "Nom Utilisateur",
        "profile": {
          "role": "journaliste",
          "full_name": "Nom Complet"
        }
      },
      "assignee": null,
      "folder": {
        "id": 1,
        "name": "Actualités Politiques"
      }
    }
    // ... autres articles
  ]
}
```

### 3. Notifications
```http
GET /api/notifications
Authorization: Bearer {token}
```

### 4. Médias
```http
GET /api/media
Authorization: Bearer {token}
```

### 5. Équipe
```http
GET /api/team/members
Authorization: Bearer {token}
```

### 6. Planning de publication
```http
GET /api/publication-schedules
Authorization: Bearer {token}
```

### 7. Logs d'audit
```http
GET /api/audit-logs
Authorization: Bearer {token}
```

## Utilisation dans React

Maintenant vous pouvez utiliser directement `.map()` sur les données :

```typescript
// Dans votre composant React
const [folders, setFolders] = useState([]);

useEffect(() => {
  const fetchFolders = async () => {
    try {
      const response = await apiService.get('/folders');
      setFolders(response.data); // response.data est maintenant un tableau
    } catch (error) {
      console.error('Erreur lors du chargement des dossiers:', error);
    }
  };
  
  fetchFolders();
}, []);

// Dans le JSX
return (
  <div>
    {folders.map(folder => (
      <div key={folder.id}>
        {folder.name}
      </div>
    ))}
  </div>
);
```

## Filtres disponibles

### Articles
- `?search=terme` - Recherche dans le titre
- `?status=draft|published|review` - Filtre par statut
- `?folder_id=1` - Filtre par dossier
- `?mine=true` - Mes articles seulement

### Équipe
- `?role=journaliste` - Filtre par rôle
- `?q=nom` - Recherche par nom

## Notes importantes

- Tous les endpoints de liste retournent maintenant des tableaux simples
- La pagination a été supprimée pour simplifier l'intégration frontend
- Les relations sont incluses (creator, assignee, folder, etc.)
- Tous les endpoints nécessitent une authentification (token Bearer)

Votre frontend devrait maintenant fonctionner sans l'erreur `folders.map is not a function` !
