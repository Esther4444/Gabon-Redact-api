# Intégration Frontend React/TypeScript - Nouvelles fonctionnalités

## 🎯 Nouvelles fonctionnalités à intégrer

### 1. **Bouton "Aperçu" dans le tableau des articles**
- Le bouton est maintenant directement visible dans la colonne Actions
- Plus besoin de passer par un menu déroulant

### 2. **Bouton "Aperçu" dans l'éditeur d'article**
- Disponible dans le header de l'éditeur
- Permet de prévisualiser l'article en cours de rédaction

### 3. **Pagination complète du tableau**
- Support de la pagination avec navigation entre pages
- Contrôles pour changer le nombre d'éléments par page

### 4. **Recherche étendue**
- Recherche dans tous les champs textuels de l'article

## 🔌 Nouvelles routes API

```typescript
// Types pour les nouvelles réponses API
interface Article {
  id: number;
  title: string;
  content: string;
  status: 'draft' | 'published' | 'review';
  slug: string;
  seo_title?: string;
  seo_description?: string;
  seo_keywords?: string[];
  created_at: string;
  updated_at: string;
  creator: {
    id: number;
    name: string;
    profile: {
      avatar?: string;
    };
  };
  assignee?: {
    id: number;
    name: string;
    profile: {
      avatar?: string;
    };
  };
  folder?: {
    id: number;
    name: string;
  };
}

interface PaginationInfo {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
  has_more_pages: boolean;
  prev_page_url: string | null;
  next_page_url: string | null;
}

interface ArticlesResponse {
  success: boolean;
  data: Article[];
  pagination: PaginationInfo;
}

interface PreviewResponse {
  success: boolean;
  data: Article;
  preview_url: string;
}
```

## 📡 Nouvelles routes API disponibles

```typescript
// 1. Liste des articles avec pagination
GET /api/articles?page=1&per_page=15&search=terme&status=draft&folder_id=1

// 2. Prévisualisation d'un article (authentifié)
GET /api/articles/{id}/preview

// 3. Prévisualisation publique
GET /api/articles/preview/{slug}
```

## 🎨 Composants React à créer/modifier

### 1. Tableau des articles avec pagination

```typescript
// components/ArticlesTable.tsx
import React, { useState, useEffect } from 'react';
import { Article, ArticlesResponse, PaginationInfo } from '../types';

interface ArticlesTableProps {
  searchTerm?: string;
  selectedStatus?: string;
  selectedFolder?: number;
}

const ArticlesTable: React.FC<ArticlesTableProps> = ({
  searchTerm = '',
  selectedStatus = '',
  selectedFolder = 0
}) => {
  const [articles, setArticles] = useState<Article[]>([]);
  const [pagination, setPagination] = useState<PaginationInfo | null>(null);
  const [currentPage, setCurrentPage] = useState(1);
  const [perPage, setPerPage] = useState(15);
  const [loading, setLoading] = useState(false);

  const loadArticles = async (page: number = 1) => {
    setLoading(true);
    try {
      const params = new URLSearchParams({
        page: page.toString(),
        per_page: perPage.toString(),
        ...(searchTerm && { search: searchTerm }),
        ...(selectedStatus && { status: selectedStatus }),
        ...(selectedFolder && { folder_id: selectedFolder.toString() })
      });

      const response = await fetch(`/api/articles?${params}`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        }
      });

      const data: ArticlesResponse = await response.json();
      
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

      const data: PreviewResponse = await response.json();
      
      if (data.success) {
        // Ouvrir la prévisualisation dans un nouvel onglet
        window.open(data.preview_url, '_blank');
      }
    } catch (error) {
      console.error('Erreur lors de la prévisualisation:', error);
    }
  };

  const handlePageChange = (page: number) => {
    loadArticles(page);
  };

  const handlePerPageChange = (newPerPage: number) => {
    setPerPage(newPerPage);
    loadArticles(1); // Retourner à la première page
  };

  useEffect(() => {
    loadArticles(currentPage);
  }, [searchTerm, selectedStatus, selectedFolder, perPage]);

  if (loading) {
    return <div className="loading">Chargement...</div>;
  }

  return (
    <div className="articles-table-container">
      <table className="articles-table">
        <thead>
          <tr>
            <th>Titre</th>
            <th>Statut</th>
            <th>Dossier</th>
            <th>Date de modification</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          {articles.map((article) => (
            <tr key={article.id}>
              <td className="article-title">
                <strong>{article.title}</strong>
                {article.seo_title && (
                  <div className="seo-title">{article.seo_title}</div>
                )}
              </td>
              <td>
                <span className={`status-badge status-${article.status}`}>
                  {article.status}
                </span>
              </td>
              <td>{article.folder?.name || 'Aucun dossier'}</td>
              <td>{new Date(article.updated_at).toLocaleDateString('fr-FR')}</td>
              <td className="actions">
                {/* Bouton Aperçu directement visible */}
                <button
                  onClick={() => previewArticle(article)}
                  className="btn-preview"
                  title="Aperçu"
                >
                  <i className="icon-eye">👁️</i>
                </button>
                
                <button
                  onClick={() => editArticle(article)}
                  className="btn-edit"
                  title="Modifier"
                >
                  <i className="icon-edit">✏️</i>
                </button>
                
                <button
                  onClick={() => deleteArticle(article)}
                  className="btn-delete"
                  title="Supprimer"
                >
                  <i className="icon-delete">🗑️</i>
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>

      {/* Pagination */}
      {pagination && pagination.total > pagination.per_page && (
        <div className="pagination">
          <button
            onClick={() => handlePageChange(pagination.current_page - 1)}
            disabled={!pagination.prev_page_url}
            className="btn-prev"
          >
            Précédent
          </button>
          
          <span className="page-info">
            Page {pagination.current_page} sur {pagination.last_page}
            ({pagination.total} articles au total)
          </span>
          
          <button
            onClick={() => handlePageChange(pagination.current_page + 1)}
            disabled={!pagination.next_page_url}
            className="btn-next"
          >
            Suivant
          </button>
          
          <select
            value={perPage}
            onChange={(e) => handlePerPageChange(Number(e.target.value))}
            className="per-page-selector"
          >
            <option value={10}>10 par page</option>
            <option value={15}>15 par page</option>
            <option value={25}>25 par page</option>
            <option value={50}>50 par page</option>
          </select>
        </div>
      )}
    </div>
  );
};

export default ArticlesTable;
```

### 2. Header de l'éditeur avec bouton Aperçu

```typescript
// components/EditorHeader.tsx
import React from 'react';
import { Article } from '../types';

interface EditorHeaderProps {
  article: Article;
  onSave: () => void;
  onPreview: () => void;
  isSaved: boolean;
  lastSaved?: string;
  wordCount: number;
}

const EditorHeader: React.FC<EditorHeaderProps> = ({
  article,
  onSave,
  onPreview,
  isSaved,
  lastSaved,
  wordCount
}) => {
  return (
    <div className="editor-header">
      <div className="editor-title-section">
        <input
          type="text"
          value={article.title}
          onChange={(e) => updateArticle({ title: e.target.value })}
          placeholder="Titre de l'article"
          className="title-input"
        />
        <span className="status-indicator">{article.status}</span>
      </div>
      
      <div className="editor-actions">
        <span className="word-count">{wordCount} mots</span>
        
        <div className={`save-status ${isSaved ? 'saved' : 'unsaved'}`}>
          <i className="icon-check">✓</i>
          {isSaved ? `Sauvegardé à ${lastSaved}` : 'Non sauvegardé'}
        </div>
        
        {/* Bouton Aperçu dans l'éditeur */}
        <button
          onClick={onPreview}
          className="btn-preview"
          disabled={!article.title.trim()}
          title="Aperçu"
        >
          <i className="icon-eye">👁️</i>
          Aperçu
        </button>
        
        <button
          onClick={onSave}
          className="btn-save"
          title="Sauvegarder"
        >
          <i className="icon-save">💾</i>
          Sauvegarder
        </button>
      </div>
    </div>
  );
};

export default EditorHeader;
```

### 3. Composant d'édition d'article

```typescript
// components/ArticleEditor.tsx
import React, { useState, useEffect } from 'react';
import EditorHeader from './EditorHeader';
import { Article, PreviewResponse } from '../types';

interface ArticleEditorProps {
  articleId?: number;
  onArticleChange?: (article: Article) => void;
}

const ArticleEditor: React.FC<ArticleEditorProps> = ({
  articleId,
  onArticleChange
}) => {
  const [article, setArticle] = useState<Article>({
    id: 0,
    title: '',
    content: '',
    status: 'draft',
    slug: '',
    created_at: '',
    updated_at: '',
    creator: { id: 0, name: '', profile: {} }
  });
  
  const [isSaved, setIsSaved] = useState(true);
  const [lastSaved, setLastSaved] = useState<string>('');
  const [wordCount, setWordCount] = useState(0);

  const updateArticle = (updates: Partial<Article>) => {
    setArticle(prev => ({ ...prev, ...updates }));
    setIsSaved(false);
    onArticleChange?.(article);
  };

  const saveArticle = async () => {
    try {
      const response = await fetch(`/api/articles/${article.id}/save`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(article)
      });

      const data = await response.json();
      
      if (data.success) {
        setArticle(data.data);
        setIsSaved(true);
        setLastSaved(new Date().toLocaleTimeString('fr-FR'));
      }
    } catch (error) {
      console.error('Erreur lors de la sauvegarde:', error);
    }
  };

  const previewArticle = async () => {
    try {
      const response = await fetch(`/api/articles/${article.id}/preview`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        }
      });

      const data: PreviewResponse = await response.json();
      
      if (data.success) {
        // Ouvrir la prévisualisation dans un nouvel onglet
        window.open(data.preview_url, '_blank');
      }
    } catch (error) {
      console.error('Erreur lors de la prévisualisation:', error);
    }
  };

  const calculateWordCount = (text: string) => {
    return text.trim().split(/\s+/).filter(word => word.length > 0).length;
  };

  useEffect(() => {
    setWordCount(calculateWordCount(article.content));
  }, [article.content]);

  // Auto-save après 2 secondes d'inactivité
  useEffect(() => {
    const timer = setTimeout(() => {
      if (!isSaved && article.title.trim()) {
        saveArticle();
      }
    }, 2000);

    return () => clearTimeout(timer);
  }, [article, isSaved]);

  return (
    <div className="article-editor">
      <EditorHeader
        article={article}
        onSave={saveArticle}
        onPreview={previewArticle}
        isSaved={isSaved}
        lastSaved={lastSaved}
        wordCount={wordCount}
      />
      
      <div className="editor-content">
        <div className="toolbar">
          {/* Barre d'outils d'édition */}
          <button className="toolbar-btn" title="Gras">
            <strong>B</strong>
          </button>
          <button className="toolbar-btn" title="Italique">
            <em>I</em>
          </button>
          <button className="toolbar-btn" title="Souligné">
            <u>U</u>
          </button>
        </div>
        
        <div className="editor-body">
          <textarea
            value={article.content}
            onChange={(e) => updateArticle({ content: e.target.value })}
            placeholder="Commencez à rédiger votre article..."
            className="content-textarea"
          />
        </div>
      </div>
    </div>
  );
};

export default ArticleEditor;
```

## 🎨 Styles CSS recommandés

```css
/* styles/ArticlesTable.css */
.articles-table-container {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.articles-table {
  width: 100%;
  border-collapse: collapse;
}

.articles-table th,
.articles-table td {
  padding: 12px 16px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.articles-table th {
  background-color: #f8f9fa;
  font-weight: 600;
  color: #495057;
}

.article-title {
  max-width: 300px;
}

.seo-title {
  font-size: 0.85em;
  color: #6c757d;
  font-style: italic;
}

.status-badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.8em;
  font-weight: 500;
}

.status-draft { background-color: #fff3cd; color: #856404; }
.status-published { background-color: #d4edda; color: #155724; }
.status-review { background-color: #cce5ff; color: #004085; }

.actions {
  display: flex;
  gap: 8px;
  align-items: center;
}

.btn-preview,
.btn-edit,
.btn-delete {
  padding: 6px 8px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 14px;
}

.btn-preview {
  background-color: #007bff;
  color: white;
}

.btn-preview:hover {
  background-color: #0056b3;
}

.btn-edit {
  background-color: #28a745;
  color: white;
}

.btn-edit:hover {
  background-color: #218838;
}

.btn-delete {
  background-color: #dc3545;
  color: white;
}

.btn-delete:hover {
  background-color: #c82333;
}

/* Pagination */
.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  margin-top: 20px;
  padding: 20px;
  background-color: #f8f9fa;
}

.btn-prev,
.btn-next {
  padding: 8px 16px;
  border: 1px solid #dee2e6;
  background-color: white;
  border-radius: 4px;
  cursor: pointer;
}

.btn-prev:hover,
.btn-next:hover {
  background-color: #e9ecef;
}

.btn-prev:disabled,
.btn-next:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  font-weight: 500;
  color: #495057;
}

.per-page-selector {
  padding: 6px 8px;
  border: 1px solid #dee2e6;
  border-radius: 4px;
  background-color: white;
}

/* styles/EditorHeader.css */
.editor-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  background: white;
  border-bottom: 1px solid #eee;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.editor-title-section {
  display: flex;
  align-items: center;
  gap: 12px;
}

.title-input {
  font-size: 18px;
  font-weight: 600;
  border: none;
  outline: none;
  background: transparent;
  min-width: 300px;
}

.status-indicator {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.8em;
  font-weight: 500;
  background-color: #fff3cd;
  color: #856404;
}

.editor-actions {
  display: flex;
  align-items: center;
  gap: 16px;
}

.word-count {
  font-size: 0.9em;
  color: #6c757d;
}

.save-status {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.9em;
}

.save-status.saved {
  color: #28a745;
}

.save-status.unsaved {
  color: #dc3545;
}

.btn-preview {
  background-color: #28a745;
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
}

.btn-preview:hover {
  background-color: #218838;
}

.btn-preview:disabled {
  background-color: #6c757d;
  cursor: not-allowed;
}

.btn-save {
  background-color: #007bff;
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
}

.btn-save:hover {
  background-color: #0056b3;
}
```

## 🔧 Hooks personnalisés recommandés

```typescript
// hooks/useArticles.ts
import { useState, useEffect } from 'react';
import { Article, ArticlesResponse } from '../types';

interface UseArticlesParams {
  page?: number;
  perPage?: number;
  search?: string;
  status?: string;
  folderId?: number;
}

export const useArticles = (params: UseArticlesParams) => {
  const [articles, setArticles] = useState<Article[]>([]);
  const [pagination, setPagination] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const loadArticles = async () => {
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

      const data: ArticlesResponse = await response.json();
      
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
  };

  useEffect(() => {
    loadArticles();
  }, [params.page, params.perPage, params.search, params.status, params.folderId]);

  return {
    articles,
    pagination,
    loading,
    error,
    refetch: loadArticles
  };
};
```

## 📋 Checklist d'intégration

- [ ] Créer les types TypeScript pour les nouvelles réponses API
- [ ] Modifier le composant de tableau pour inclure le bouton "Aperçu" direct
- [ ] Ajouter la pagination avec contrôles de navigation
- [ ] Intégrer le bouton "Aperçu" dans l'header de l'éditeur
- [ ] Implémenter la logique de prévisualisation
- [ ] Ajouter les styles CSS pour les nouveaux éléments
- [ ] Tester la pagination avec différents paramètres
- [ ] Tester la prévisualisation des articles
- [ ] Vérifier la responsivité sur mobile

## 🚀 Exemple d'utilisation complète

```typescript
// App.tsx
import React, { useState } from 'react';
import ArticlesTable from './components/ArticlesTable';
import ArticleEditor from './components/ArticleEditor';

const App: React.FC = () => {
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedStatus, setSelectedStatus] = useState('');
  const [selectedFolder, setSelectedFolder] = useState<number>(0);
  const [editingArticle, setEditingArticle] = useState<number | null>(null);

  return (
    <div className="app">
      <header className="app-header">
        <h1>RedacGabon Pro</h1>
        <div className="filters">
          <input
            type="text"
            placeholder="Rechercher des articles..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
          <select
            value={selectedStatus}
            onChange={(e) => setSelectedStatus(e.target.value)}
          >
            <option value="">Tous les statuts</option>
            <option value="draft">Brouillon</option>
            <option value="published">Publié</option>
            <option value="review">En révision</option>
          </select>
        </div>
      </header>

      <main className="app-main">
        {editingArticle ? (
          <ArticleEditor
            articleId={editingArticle}
            onArticleChange={() => {}}
          />
        ) : (
          <ArticlesTable
            searchTerm={searchTerm}
            selectedStatus={selectedStatus}
            selectedFolder={selectedFolder}
          />
        )}
      </main>
    </div>
  );
};

export default App;
```

Cette documentation vous donne tout ce qu'il faut pour intégrer les nouvelles fonctionnalités dans votre application React/TypeScript !
