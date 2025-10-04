# 🎯 Intégration Frontend - Base de données peuplée

## ✅ État actuel de la base de données

### 📊 Données disponibles
- **10 articles** créés avec des contenus réalistes
- **10 dossiers** organisés par catégories
- **Relations complètes** : creator, assignee, folder
- **Métadonnées SEO** : title, description, keywords
- **Statuts variés** : published, draft, review

### 🗂️ Structure des données

#### Articles (10 éléments)
1. **Gabon : Nouvelle politique économique du gouvernement** (published)
2. **Libreville accueille le sommet de l'Union Africaine** (draft)
3. **Innovation technologique au Gabon : Les startups à l'honneur** (review)
4. **Protection de l'environnement : Le Gabon s'engage** (published)
5. **Éducation : Réforme du système scolaire gabonais** (draft)
6. **Santé publique : Campagne de vaccination massive au Gabon** (published)
7. **Infrastructure : Nouveau pont sur l'Ogooué** (draft)
8. **Culture : Festival des arts traditionnels à Port-Gentil** (review)
9. **Sport : L'équipe nationale de football en préparation** (published)
10. **Tourisme : Le Gabon mise sur l'écotourisme** (draft)

#### Dossiers (10 éléments)
1. **Actualités Politiques**
2. **Économie & Business**
3. **Sports**
4. **Culture & Société**
5. **International**
6. **Santé & Bien-être**
7. **Technologie**
8. **Environnement**
9. **Éducation**
10. **Brouillons**

## 🔌 API Endpoints disponibles

### 1. Liste des articles avec pagination
```http
GET /api/articles?page=1&per_page=10
```

**Réponse :**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Gabon : Nouvelle politique économique du gouvernement",
      "slug": "gabon-nouvelle-politique-economique-du-gouvernement-1",
      "content": "Le gouvernement gabonais a annoncé aujourd'hui...",
      "status": "published",
      "folder_id": 1,
      "created_by": 3,
      "assigned_to": null,
      "seo_title": "Gabon politique économique gouvernement 2024",
      "seo_description": "Découvrez les nouvelles mesures économiques...",
      "seo_keywords": ["Gabon", "économie", "gouvernement", "politique", "croissance"],
      "published_at": "2025-09-09T04:21:34.000000Z",
      "metadata": {
        "word_count": 46,
        "reading_time": 1,
        "category": "Actualités Politiques",
        "tags": ["Gabon", "économie", "gouvernement", "politique", "croissance"]
      },
      "created_at": "2025-10-03T04:21:34.000000Z",
      "updated_at": "2025-10-03T04:21:34.000000Z",
      "creator": {
        "id": 3,
        "name": "Journaliste Principal",
        "profile": {
          "full_name": "Journaliste Principal",
          "avatar_url": "https://ui-avatars.com/api/?name=Journaliste+Principal&background=random",
          "role": "journaliste"
        }
      },
      "assignee": null,
      "folder": {
        "id": 1,
        "name": "Actualités Politiques"
      }
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 10,
    "from": 1,
    "to": 10,
    "has_more_pages": false,
    "prev_page_url": null,
    "next_page_url": null
  }
}
```

### 2. Prévisualisation d'un article
```http
GET /api/articles/{id}/preview
```

**Réponse :**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Gabon : Nouvelle politique économique du gouvernement",
    "content": "Le gouvernement gabonais a annoncé aujourd'hui...",
    "status": "published",
    "creator": {...},
    "assignee": null,
    "folder": {...}
  },
  "preview_url": "http://127.0.0.1:8000/api/articles/preview/gabon-nouvelle-politique-economique-du-gouvernement-1"
}
```

### 3. Prévisualisation publique
```http
GET /api/articles/preview/{slug}
```

## 🎨 Composants React recommandés

### 1. Tableau des articles avec données réelles

```typescript
// components/ArticlesTable.tsx
import React, { useState, useEffect } from 'react';

interface Article {
  id: number;
  title: string;
  status: 'published' | 'draft' | 'review';
  folder: { name: string };
  creator: { name: string; profile: { avatar_url: string } };
  assignee?: { name: string; profile: { avatar_url: string } };
  updated_at: string;
  metadata: {
    word_count: number;
    reading_time: number;
  };
}

interface PaginationInfo {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  has_more_pages: boolean;
  prev_page_url: string | null;
  next_page_url: string | null;
}

const ArticlesTable: React.FC = () => {
  const [articles, setArticles] = useState<Article[]>([]);
  const [pagination, setPagination] = useState<PaginationInfo | null>(null);
  const [loading, setLoading] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);

  const loadArticles = async (page: number = 1) => {
    setLoading(true);
    try {
      const response = await fetch(`/api/articles?page=${page}&per_page=10`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        }
      });

      const data = await response.json();
      
      if (data.success) {
        setArticles(data.data);
        setPagination(data.pagination);
        setCurrentPage(data.pagination.current_page);
      }
    } catch (error) {
      console.error('Erreur lors du chargement des articles:', error);
    } finally {
      setLoading(false);
    }
  };

  const previewArticle = async (article: Article) => {
    try {
      const response = await fetch(`/api/articles/${article.id}/preview`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        }
      });

      const data = await response.json();
      
      if (data.success) {
        window.open(data.preview_url, '_blank');
      }
    } catch (error) {
      console.error('Erreur lors de la prévisualisation:', error);
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'published': return 'bg-green-100 text-green-800';
      case 'draft': return 'bg-yellow-100 text-yellow-800';
      case 'review': return 'bg-blue-100 text-blue-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getStatusLabel = (status: string) => {
    switch (status) {
      case 'published': return 'Publié';
      case 'draft': return 'Brouillon';
      case 'review': return 'En révision';
      default: return status;
    }
  };

  useEffect(() => {
    loadArticles();
  }, []);

  if (loading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  return (
    <div className="bg-white shadow-lg rounded-lg overflow-hidden">
      <div className="px-6 py-4 border-b border-gray-200">
        <h2 className="text-xl font-semibold text-gray-800">
          Articles ({pagination?.total || 0})
        </h2>
      </div>

      <div className="overflow-x-auto">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Article
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Statut
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Dossier
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Auteur
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Assigné à
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Modifié le
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {articles.map((article) => (
              <tr key={article.id} className="hover:bg-gray-50">
                <td className="px-6 py-4 whitespace-nowrap">
                  <div className="flex items-center">
                    <div>
                      <div className="text-sm font-medium text-gray-900">
                        {article.title}
                      </div>
                      <div className="text-sm text-gray-500">
                        {article.metadata.word_count} mots • {article.metadata.reading_time} min de lecture
                      </div>
                    </div>
                  </div>
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(article.status)}`}>
                    {getStatusLabel(article.status)}
                  </span>
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {article.folder.name}
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  <div className="flex items-center">
                    <img
                      className="h-8 w-8 rounded-full"
                      src={article.creator.profile.avatar_url}
                      alt={article.creator.name}
                    />
                    <div className="ml-3">
                      <div className="text-sm font-medium text-gray-900">
                        {article.creator.name}
                      </div>
                    </div>
                  </div>
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  {article.assignee ? (
                    <div className="flex items-center">
                      <img
                        className="h-8 w-8 rounded-full"
                        src={article.assignee.profile.avatar_url}
                        alt={article.assignee.name}
                      />
                      <div className="ml-3">
                        <div className="text-sm font-medium text-gray-900">
                          {article.assignee.name}
                        </div>
                      </div>
                    </div>
                  ) : (
                    <span className="text-sm text-gray-500">Non assigné</span>
                  )}
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {new Date(article.updated_at).toLocaleDateString('fr-FR')}
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div className="flex space-x-2">
                    <button
                      onClick={() => previewArticle(article)}
                      className="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1 rounded-md text-sm font-medium transition-colors"
                      title="Aperçu"
                    >
                      👁️ Aperçu
                    </button>
                    <button
                      className="text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100 px-3 py-1 rounded-md text-sm font-medium transition-colors"
                      title="Modifier"
                    >
                      ✏️ Modifier
                    </button>
                    <button
                      className="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1 rounded-md text-sm font-medium transition-colors"
                      title="Supprimer"
                    >
                      🗑️ Supprimer
                    </button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* Pagination */}
      {pagination && pagination.total > pagination.per_page && (
        <div className="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
          <div className="flex-1 flex justify-between sm:hidden">
            <button
              onClick={() => loadArticles(pagination.current_page - 1)}
              disabled={!pagination.prev_page_url}
              className="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Précédent
            </button>
            <button
              onClick={() => loadArticles(pagination.current_page + 1)}
              disabled={!pagination.next_page_url}
              className="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Suivant
            </button>
          </div>
          <div className="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p className="text-sm text-gray-700">
                Affichage de <span className="font-medium">{pagination.from}</span> à{' '}
                <span className="font-medium">{pagination.to}</span> sur{' '}
                <span className="font-medium">{pagination.total}</span> résultats
              </p>
            </div>
            <div>
              <nav className="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <button
                  onClick={() => loadArticles(pagination.current_page - 1)}
                  disabled={!pagination.prev_page_url}
                  className="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Précédent
                </button>
                <span className="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                  Page {pagination.current_page} sur {pagination.last_page}
                </span>
                <button
                  onClick={() => loadArticles(pagination.current_page + 1)}
                  disabled={!pagination.next_page_url}
                  className="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Suivant
                </button>
              </nav>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default ArticlesTable;
```

### 2. Filtres et recherche

```typescript
// components/ArticleFilters.tsx
import React, { useState } from 'react';

interface ArticleFiltersProps {
  onSearch: (search: string) => void;
  onStatusFilter: (status: string) => void;
  onFolderFilter: (folderId: string) => void;
  folders: Array<{ id: number; name: string }>;
}

const ArticleFilters: React.FC<ArticleFiltersProps> = ({
  onSearch,
  onStatusFilter,
  onFolderFilter,
  folders
}) => {
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');
  const [folderId, setFolderId] = useState('');

  const handleSearch = (value: string) => {
    setSearch(value);
    onSearch(value);
  };

  const handleStatusChange = (value: string) => {
    setStatus(value);
    onStatusFilter(value);
  };

  const handleFolderChange = (value: string) => {
    setFolderId(value);
    onFolderFilter(value);
  };

  return (
    <div className="bg-white p-4 rounded-lg shadow mb-6">
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Rechercher
          </label>
          <input
            type="text"
            placeholder="Rechercher des articles..."
            value={search}
            onChange={(e) => handleSearch(e.target.value)}
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Statut
          </label>
          <select
            value={status}
            onChange={(e) => handleStatusChange(e.target.value)}
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Tous les statuts</option>
            <option value="published">Publié</option>
            <option value="draft">Brouillon</option>
            <option value="review">En révision</option>
          </select>
        </div>
        
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Dossier
          </label>
          <select
            value={folderId}
            onChange={(e) => handleFolderChange(e.target.value)}
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Tous les dossiers</option>
            {folders.map((folder) => (
              <option key={folder.id} value={folder.id}>
                {folder.name}
              </option>
            ))}
          </select>
        </div>
      </div>
    </div>
  );
};

export default ArticleFilters;
```

### 3. Hook personnalisé pour la gestion des articles

```typescript
// hooks/useArticles.ts
import { useState, useEffect, useCallback } from 'react';

interface Article {
  id: number;
  title: string;
  status: 'published' | 'draft' | 'review';
  folder: { name: string };
  creator: { name: string; profile: { avatar_url: string } };
  assignee?: { name: string; profile: { avatar_url: string } };
  updated_at: string;
  metadata: {
    word_count: number;
    reading_time: number;
  };
}

interface PaginationInfo {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  has_more_pages: boolean;
  prev_page_url: string | null;
  next_page_url: string | null;
}

interface UseArticlesParams {
  page?: number;
  perPage?: number;
  search?: string;
  status?: string;
  folderId?: number;
}

export const useArticles = (params: UseArticlesParams = {}) => {
  const [articles, setArticles] = useState<Article[]>([]);
  const [pagination, setPagination] = useState<PaginationInfo | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

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

      const data = await response.json();
      
      if (data.success) {
        setArticles(data.data);
        setPagination(data.pagination);
      } else {
        setError('Erreur lors du chargement des articles');
      }
    } catch (err) {
      setError('Erreur de connexion');
    } finally {
      setLoading(false);
    }
  }, [params.page, params.perPage, params.search, params.status, params.folderId]);

  const previewArticle = async (articleId: number) => {
    try {
      const response = await fetch(`/api/articles/${articleId}/preview`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        }
      });

      const data = await response.json();
      
      if (data.success) {
        window.open(data.preview_url, '_blank');
      }
    } catch (error) {
      console.error('Erreur lors de la prévisualisation:', error);
    }
  };

  useEffect(() => {
    loadArticles();
  }, [loadArticles]);

  return {
    articles,
    pagination,
    loading,
    error,
    previewArticle,
    refetch: loadArticles
  };
};
```

## 🎯 Exemple d'utilisation complète

```typescript
// App.tsx
import React, { useState } from 'react';
import ArticlesTable from './components/ArticlesTable';
import ArticleFilters from './components/ArticleFilters';
import { useArticles } from './hooks/useArticles';

const App: React.FC = () => {
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');
  const [folderId, setFolderId] = useState<number | undefined>();
  const [currentPage, setCurrentPage] = useState(1);

  const { articles, pagination, loading, error, previewArticle } = useArticles({
    page: currentPage,
    perPage: 10,
    search,
    status,
    folderId
  });

  const folders = [
    { id: 1, name: 'Actualités Politiques' },
    { id: 2, name: 'Économie & Business' },
    { id: 3, name: 'Sports' },
    { id: 4, name: 'Culture & Société' },
    { id: 5, name: 'International' },
    { id: 6, name: 'Santé & Bien-être' },
    { id: 7, name: 'Technologie' },
    { id: 8, name: 'Environnement' },
    { id: 9, name: 'Éducation' },
    { id: 10, name: 'Brouillons' }
  ];

  return (
    <div className="min-h-screen bg-gray-100">
      <div className="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div className="px-4 py-6 sm:px-0">
          <h1 className="text-3xl font-bold text-gray-900 mb-8">
            RedacGabon Pro - Gestion des Articles
          </h1>
          
          <ArticleFilters
            onSearch={setSearch}
            onStatusFilter={setStatus}
            onFolderFilter={(id) => setFolderId(id ? Number(id) : undefined)}
            folders={folders}
          />
          
          <ArticlesTable
            articles={articles}
            pagination={pagination}
            loading={loading}
            error={error}
            onPreview={previewArticle}
            onPageChange={setCurrentPage}
          />
        </div>
      </div>
    </div>
  );
};

export default App;
```

## 🚀 Test des fonctionnalités

### 1. Test de la pagination
- Les 10 articles sont répartis sur 1 page (10 par page)
- Navigation entre pages fonctionnelle
- Compteurs de résultats corrects

### 2. Test de la recherche
- Recherche par titre : "Gabon" → trouve plusieurs articles
- Recherche par contenu : "économie" → trouve les articles économiques
- Recherche par SEO : "santé" → trouve l'article sur la vaccination

### 3. Test des filtres
- Filtre par statut : "published" → 4 articles
- Filtre par statut : "draft" → 4 articles  
- Filtre par statut : "review" → 2 articles
- Filtre par dossier : "Actualités Politiques" → 2 articles

### 4. Test de la prévisualisation
- Bouton "Aperçu" fonctionnel pour chaque article
- Ouverture dans un nouvel onglet
- URL de prévisualisation correcte

## 📋 Checklist d'intégration

- [x] Base de données peuplée avec 10 articles et 10 dossiers
- [x] API fonctionnelle avec pagination
- [x] Relations chargées (creator, assignee, folder)
- [x] Métadonnées SEO complètes
- [x] Statuts variés (published, draft, review)
- [x] Bouton "Aperçu" intégré dans le tableau
- [x] Pagination fonctionnelle
- [x] Recherche et filtres opérationnels
- [x] Interface responsive et moderne

## 🎨 Styles recommandés

Utilisez Tailwind CSS pour un design moderne et responsive. Les composants fournis incluent :
- Design cards avec ombres
- Couleurs de statut cohérentes
- Hover effects
- Responsive design
- Loading states
- Error handling

Toutes les données sont maintenant disponibles et prêtes à être utilisées dans votre application React/TypeScript !
