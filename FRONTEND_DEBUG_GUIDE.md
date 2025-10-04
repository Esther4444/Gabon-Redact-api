# 🐛 Guide de débogage - Erreur ERR_INSUFFICIENT_RESOURCES

## 🚨 Problème identifié

**Erreur :** `ERR_INSUFFICIENT_RESOURCES` + `Failed to fetch`
**Cause :** Boucle infinie dans le code frontend qui fait trop de requêtes simultanées

## 🔍 Diagnostic

### 1. **Vérifier le serveur Laravel**
```bash
# Le serveur doit être démarré
php artisan serve --host=127.0.0.1 --port=8000

# Tester l'API (retourne 401 car pas d'auth, mais c'est normal)
curl http://127.0.0.1:8000/api/articles
```

### 2. **Problèmes courants dans useArticles.ts**

#### ❌ **Problème 1 : useEffect sans dépendances**
```typescript
// MAUVAIS - Cause une boucle infinie
useEffect(() => {
  loadArticles(); // Cette fonction change à chaque render
}, [loadArticles]); // loadArticles change à chaque render
```

#### ❌ **Problème 2 : Fonction non mémorisée**
```typescript
// MAUVAIS - loadArticles est recréée à chaque render
const loadArticles = async () => {
  // ... logique
};

useEffect(() => {
  loadArticles();
}, [loadArticles]); // Boucle infinie !
```

#### ❌ **Problème 3 : Dépendances qui changent constamment**
```typescript
// MAUVAIS - params change à chaque render
useEffect(() => {
  loadArticles();
}, [params]); // params est un objet qui change constamment
```

## ✅ **Solutions**

### 1. **Corriger useArticles.ts**

```typescript
// hooks/useArticles.ts - VERSION CORRIGÉE
import { useState, useEffect, useCallback } from 'react';

interface UseArticlesParams {
  page?: number;
  perPage?: number;
  search?: string;
  status?: string;
  folderId?: number;
}

export const useArticles = (params: UseArticlesParams = {}) => {
  const [articles, setArticles] = useState([]);
  const [pagination, setPagination] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  // Mémoriser la fonction loadArticles
  const loadArticles = useCallback(async () => {
    setLoading(true);
    setError(null);
    
    try {
      const searchParams = new URLSearchParams();
      if (params.page) searchParams.set('page', params.page.toString());
      if (params.perPage) searchParams.set('per_page', params.perPage.toString());
      if (params.search) searchParams.set('search', params.search);
      if (params.status) searchParams.set('status', params.status);
      if (params.folderId) searchParams.set('folder_id', params.folderId.toString());

      const response = await fetch(`/api/articles?${searchParams}`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        }
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      
      if (data.success) {
        setArticles(data.data);
        setPagination(data.pagination);
      } else {
        setError('Erreur lors du chargement des articles');
      }
    } catch (err) {
      console.error('Erreur lors du chargement des articles:', err);
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }, [params.page, params.perPage, params.search, params.status, params.folderId]);

  // Utiliser useEffect avec les bonnes dépendances
  useEffect(() => {
    loadArticles();
  }, [loadArticles]);

  return {
    articles,
    pagination,
    loading,
    error,
    refetch: loadArticles
  };
};
```

### 2. **Corriger le composant Articles.tsx**

```typescript
// components/Articles.tsx - VERSION CORRIGÉE
import React, { useState, useMemo } from 'react';
import { useArticles } from '../hooks/useArticles';

const Articles: React.FC = () => {
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');
  const [folderId, setFolderId] = useState<number | undefined>();
  const [currentPage, setCurrentPage] = useState(1);

  // Mémoriser les paramètres pour éviter les re-renders
  const params = useMemo(() => ({
    page: currentPage,
    perPage: 10,
    search,
    status,
    folderId
  }), [currentPage, search, status, folderId]);

  const { articles, pagination, loading, error, refetch } = useArticles(params);

  // Fonction pour gérer les changements de recherche avec debounce
  const handleSearchChange = (value: string) => {
    setSearch(value);
    setCurrentPage(1); // Retourner à la première page
  };

  const handleStatusChange = (value: string) => {
    setStatus(value);
    setCurrentPage(1);
  };

  const handleFolderChange = (value: number | undefined) => {
    setFolderId(value);
    setCurrentPage(1);
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="bg-red-50 border border-red-200 rounded-md p-4">
        <div className="flex">
          <div className="ml-3">
            <h3 className="text-sm font-medium text-red-800">
              Erreur de chargement
            </h3>
            <div className="mt-2 text-sm text-red-700">
              {error}
            </div>
            <div className="mt-4">
              <button
                onClick={refetch}
                className="bg-red-100 px-3 py-2 rounded-md text-sm font-medium text-red-800 hover:bg-red-200"
              >
                Réessayer
              </button>
            </div>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div>
      {/* Filtres */}
      <div className="mb-6">
        <input
          type="text"
          placeholder="Rechercher des articles..."
          value={search}
          onChange={(e) => handleSearchChange(e.target.value)}
          className="w-full px-3 py-2 border border-gray-300 rounded-md"
        />
        
        <div className="mt-4 flex space-x-4">
          <select
            value={status}
            onChange={(e) => handleStatusChange(e.target.value)}
            className="px-3 py-2 border border-gray-300 rounded-md"
          >
            <option value="">Tous les statuts</option>
            <option value="published">Publié</option>
            <option value="draft">Brouillon</option>
            <option value="review">En révision</option>
          </select>
        </div>
      </div>

      {/* Tableau */}
      <div className="bg-white shadow overflow-hidden sm:rounded-md">
        <ul className="divide-y divide-gray-200">
          {articles.map((article) => (
            <li key={article.id} className="px-6 py-4">
              <div className="flex items-center justify-between">
                <div className="flex-1">
                  <h3 className="text-lg font-medium text-gray-900">
                    {article.title}
                  </h3>
                  <p className="text-sm text-gray-500">
                    {article.folder?.name} • {article.creator?.name}
                  </p>
                </div>
                <div className="flex items-center space-x-2">
                  <span className={`px-2 py-1 text-xs font-semibold rounded-full ${
                    article.status === 'published' ? 'bg-green-100 text-green-800' :
                    article.status === 'draft' ? 'bg-yellow-100 text-yellow-800' :
                    'bg-blue-100 text-blue-800'
                  }`}>
                    {article.status}
                  </span>
                  <button
                    onClick={() => previewArticle(article.id)}
                    className="text-blue-600 hover:text-blue-900"
                  >
                    Aperçu
                  </button>
                </div>
            </div>
            </li>
          ))}
        </ul>
      </div>

      {/* Pagination */}
      {pagination && pagination.total > pagination.per_page && (
        <div className="mt-6 flex items-center justify-between">
          <div className="text-sm text-gray-700">
            Page {pagination.current_page} sur {pagination.last_page}
          </div>
          <div className="flex space-x-2">
            <button
              onClick={() => setCurrentPage(pagination.current_page - 1)}
              disabled={!pagination.prev_page_url}
              className="px-3 py-2 border border-gray-300 rounded-md disabled:opacity-50"
            >
              Précédent
            </button>
            <button
              onClick={() => setCurrentPage(pagination.current_page + 1)}
              disabled={!pagination.next_page_url}
              className="px-3 py-2 border border-gray-300 rounded-md disabled:opacity-50"
            >
              Suivant
            </button>
          </div>
        </div>
      )}
    </div>
  );
};

export default Articles;
```

### 3. **Ajouter un debounce pour la recherche**

```typescript
// hooks/useDebounce.ts
import { useState, useEffect } from 'react';

export function useDebounce<T>(value: T, delay: number): T {
  const [debouncedValue, setDebouncedValue] = useState<T>(value);

  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedValue(value);
    }, delay);

    return () => {
      clearTimeout(handler);
    };
  }, [value, delay]);

  return debouncedValue;
}
```

```typescript
// Dans Articles.tsx - Utiliser le debounce
import { useDebounce } from '../hooks/useDebounce';

const Articles: React.FC = () => {
  const [search, setSearch] = useState('');
  const debouncedSearch = useDebounce(search, 500); // Attendre 500ms

  const params = useMemo(() => ({
    page: currentPage,
    perPage: 10,
    search: debouncedSearch, // Utiliser la valeur debounced
    status,
    folderId
  }), [currentPage, debouncedSearch, status, folderId]);

  // ... reste du code
};
```

## 🛠️ **Étapes de débogage**

### 1. **Vérifier la console du navigateur**
- Ouvrir les DevTools (F12)
- Aller dans l'onglet "Network"
- Voir si les requêtes se répètent en boucle

### 2. **Ajouter des logs de débogage**
```typescript
const loadArticles = useCallback(async () => {
  console.log('🔄 Chargement des articles...', params);
  setLoading(true);
  // ... reste du code
}, [params.page, params.perPage, params.search, params.status, params.folderId]);
```

### 3. **Vérifier les dépendances useEffect**
```typescript
useEffect(() => {
  console.log('📊 useEffect déclenché', { params, loadArticles });
  loadArticles();
}, [loadArticles]);
```

## 🚀 **Test de l'API**

### 1. **Tester avec Postman ou curl**
```bash
# Tester l'authentification d'abord
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@redacgabon.com","password":"password"}'

# Utiliser le token retourné
curl -X GET http://127.0.0.1:8000/api/articles \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### 2. **Vérifier les logs Laravel**
```bash
tail -f storage/logs/laravel.log
```

## ⚠️ **Points d'attention**

1. **Ne jamais mettre des objets dans les dépendances useEffect**
2. **Toujours mémoriser les fonctions avec useCallback**
3. **Utiliser useMemo pour les objets complexes**
4. **Ajouter un debounce pour la recherche**
5. **Gérer les erreurs de réseau correctement**

## 🔧 **Solution rapide**

Si le problème persiste, remplacez temporairement `useArticles` par :

```typescript
const useArticles = (params: UseArticlesParams = {}) => {
  const [articles, setArticles] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  // Charger une seule fois au montage
  useEffect(() => {
    const loadData = async () => {
      setLoading(true);
      try {
        const response = await fetch('/api/articles?page=1&per_page=10', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        });
        const data = await response.json();
        if (data.success) {
          setArticles(data.data);
        }
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };
    
    loadData();
  }, []); // Dépendances vides = une seule fois

  return { articles, loading, error };
};
```

Cette version simple devrait résoudre le problème de boucle infinie !
