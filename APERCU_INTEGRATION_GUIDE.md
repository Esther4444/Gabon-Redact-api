# Guide d'intégration du bouton "Aperçu" et de la pagination

## Modifications apportées au backend

### 1. Contrôleur Article (`app/Http/Controllers/ArticleController.php`)

#### Pagination ajoutée dans la méthode `index()`
- Support de la pagination avec `per_page` et `page` en paramètres
- Par défaut : 15 articles par page
- Recherche étendue dans le titre, contenu, SEO title et SEO description
- Tri par date de modification décroissante
- Retourne les données avec les informations de pagination complètes

#### Nouvelles méthodes ajoutées
- `preview(Article $article)` : Prévisualisation pour utilisateurs authentifiés
- `publicPreview($slug)` : Prévisualisation publique accessible via slug

### 2. Routes API (`routes/api.php`)

#### Nouvelles routes ajoutées
```php
// Route publique pour la prévisualisation
Route::get('articles/preview/{slug}', [ArticleController::class, 'publicPreview']);

// Route authentifiée pour la prévisualisation
Route::get('articles/{article}/preview', [ArticleController::class, 'preview']);
```

## Structure de réponse API

### Liste des articles avec pagination
```json
{
  "success": true,
  "data": [...], // Articles de la page courante
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 67,
    "from": 1,
    "to": 15,
    "has_more_pages": true,
    "prev_page_url": null,
    "next_page_url": "http://api/articles?page=2"
  }
}
```

### Prévisualisation d'article
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Titre de l'article",
    "content": "Contenu...",
    "status": "draft",
    "slug": "titre-de-larticle",
    "creator": {...},
    "assignee": {...},
    "folder": {...}
  },
  "preview_url": "http://api/articles/preview/titre-de-larticle"
}
```

## Intégration Frontend

### 1. Tableau des articles avec bouton "Aperçu"

```html
<!-- Tableau des articles -->
<table class="articles-table">
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
    <tr v-for="article in articles" :key="article.id">
      <td>{{ article.title }}</td>
      <td>
        <span class="status-badge" :class="article.status">
          {{ article.status }}
        </span>
      </td>
      <td>{{ article.folder?.name || 'Aucun dossier' }}</td>
      <td>{{ formatDate(article.updated_at) }}</td>
      <td class="actions">
        <!-- Bouton Aperçu directement visible -->
        <button @click="previewArticle(article)" class="btn-preview" title="Aperçu">
          <i class="icon-eye"></i>
        </button>
        
        <!-- Bouton Modifier -->
        <button @click="editArticle(article)" class="btn-edit" title="Modifier">
          <i class="icon-edit"></i>
        </button>
        
        <!-- Menu déroulant pour autres actions -->
        <div class="dropdown">
          <button class="btn-more" title="Plus d'actions">
            <i class="icon-more"></i>
          </button>
          <div class="dropdown-menu">
            <a @click="duplicateArticle(article)">Dupliquer</a>
            <a @click="deleteArticle(article)" class="text-danger">Supprimer</a>
          </div>
        </div>
      </td>
    </tr>
  </tbody>
</table>

<!-- Pagination -->
<div class="pagination" v-if="pagination.total > pagination.per_page">
  <button 
    @click="loadPage(pagination.current_page - 1)"
    :disabled="!pagination.prev_page_url"
    class="btn-prev"
  >
    Précédent
  </button>
  
  <span class="page-info">
    Page {{ pagination.current_page }} sur {{ pagination.last_page }}
    ({{ pagination.total }} articles au total)
  </span>
  
  <button 
    @click="loadPage(pagination.current_page + 1)"
    :disabled="!pagination.next_page_url"
    class="btn-next"
  >
    Suivant
  </button>
  
  <!-- Sélecteur d'éléments par page -->
  <select @change="changePerPage" v-model="perPage">
    <option value="10">10 par page</option>
    <option value="15">15 par page</option>
    <option value="25">25 par page</option>
    <option value="50">50 par page</option>
  </select>
</div>
```

### 2. JavaScript/Vue.js pour la gestion

```javascript
// Méthodes pour la gestion des articles
methods: {
  // Charger les articles avec pagination
  async loadArticles(page = 1) {
    try {
      const response = await axios.get('/api/articles', {
        params: {
          page: page,
          per_page: this.perPage,
          search: this.searchTerm,
          status: this.selectedStatus,
          folder_id: this.selectedFolder
        }
      });
      
      this.articles = response.data.data;
      this.pagination = response.data.pagination;
    } catch (error) {
      console.error('Erreur lors du chargement des articles:', error);
    }
  },

  // Prévisualisation d'un article
  async previewArticle(article) {
    try {
      const response = await axios.get(`/api/articles/${article.id}/preview`);
      const previewData = response.data.data;
      
      // Ouvrir la prévisualisation dans un nouvel onglet
      window.open(response.data.preview_url, '_blank');
      
      // Ou afficher dans une modal
      this.showPreviewModal(previewData);
    } catch (error) {
      console.error('Erreur lors de la prévisualisation:', error);
    }
  },

  // Afficher la prévisualisation dans une modal
  showPreviewModal(article) {
    this.previewArticle = article;
    this.showPreview = true;
  },

  // Changer de page
  loadPage(page) {
    this.loadArticles(page);
  },

  // Changer le nombre d'éléments par page
  changePerPage() {
    this.loadArticles(1); // Retourner à la première page
  }
}
```

### 3. Interface de rédaction avec bouton "Aperçu"

```html
<!-- Header de l'éditeur -->
<div class="editor-header">
  <div class="editor-title">
    <input v-model="article.title" placeholder="Titre de l'article" class="title-input">
    <span class="status-indicator">{{ article.status }}</span>
  </div>
  
  <div class="editor-actions">
    <span class="word-count">{{ wordCount }} mots</span>
    
    <div class="save-status" :class="{ 'saved': isSaved }">
      <i class="icon-check"></i>
      Sauvegardé à {{ lastSaved }}
    </div>
    
    <!-- Bouton Aperçu dans l'éditeur -->
    <button @click="previewCurrentArticle" class="btn-preview" :disabled="!article.title">
      <i class="icon-eye"></i>
      Aperçu
    </button>
    
    <button @click="saveArticle" class="btn-save">
      <i class="icon-save"></i>
      Sauvegarder
    </button>
  </div>
</div>

<!-- Zone d'édition -->
<div class="editor-content">
  <div class="toolbar">
    <!-- Barre d'outils d'édition -->
  </div>
  
  <div class="editor-body">
    <textarea 
      v-model="article.content" 
      placeholder="Commencez à rédiger votre article..."
      @input="autoSave"
    ></textarea>
  </div>
</div>
```

### 4. CSS pour le style

```css
/* Styles pour le tableau */
.articles-table {
  width: 100%;
  border-collapse: collapse;
}

.articles-table th,
.articles-table td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.actions {
  display: flex;
  gap: 8px;
  align-items: center;
}

.btn-preview,
.btn-edit,
.btn-more {
  padding: 6px 8px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-preview {
  background-color: #007bff;
  color: white;
}

.btn-preview:hover {
  background-color: #0056b3;
}

/* Pagination */
.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  margin-top: 20px;
  padding: 20px;
}

.page-info {
  font-weight: 500;
}

/* Header de l'éditeur */
.editor-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  background: white;
  border-bottom: 1px solid #eee;
}

.editor-actions {
  display: flex;
  align-items: center;
  gap: 16px;
}

.btn-preview {
  background-color: #28a745;
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn-preview:hover {
  background-color: #218838;
}

.btn-preview:disabled {
  background-color: #6c757d;
  cursor: not-allowed;
}
```

## Utilisation des nouvelles fonctionnalités

### 1. Récupération des articles avec pagination
```javascript
// Charger la première page avec 15 articles par défaut
GET /api/articles

// Charger la page 2 avec 25 articles par page
GET /api/articles?page=2&per_page=25

// Rechercher avec filtres
GET /api/articles?search=test&status=draft&folder_id=1
```

### 2. Prévisualisation d'un article
```javascript
// Prévisualisation pour utilisateur authentifié
GET /api/articles/123/preview

// Prévisualisation publique
GET /api/articles/preview/mon-article-slug
```

### 3. Gestion de la pagination côté frontend
- Utiliser les informations de `pagination` pour afficher les contrôles
- Implémenter la navigation entre pages
- Permettre le changement du nombre d'éléments par page
- Gérer les états de chargement et d'erreur

Cette implémentation permet d'avoir un tableau complet avec pagination, un bouton "Aperçu" directement accessible, et une intégration dans l'éditeur d'articles, le tout connecté à la base de données Laravel.
