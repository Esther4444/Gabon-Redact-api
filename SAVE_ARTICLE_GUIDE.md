# Guide de Sauvegarde d'Articles - RedacGabonPro

## 🎯 **Objectif**
Modifier le comportement de sauvegarde pour qu'elle se fasse uniquement manuellement (bouton "Sauvegarder") et supprimer l'enregistrement automatique.

## 🔧 **Modifications API**

### 1. **Nouvel endpoint de sauvegarde**
```http
POST /api/articles/{id}/save
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Titre de l'article",
  "content": "Contenu de l'article...",
  "folder_id": 1,
  "seo_title": "SEO Title",
  "seo_description": "Description SEO",
  "seo_keywords": ["mot1", "mot2"],
  "status": "draft"
}
```

**Réponse :**
```json
{
  "success": true,
  "message": "Article sauvegardé avec succès",
  "data": {
    "id": 1,
    "title": "Titre de l'article",
    "content": "Contenu de l'article...",
    "status": "draft",
    "slug": "titre-de-larticle",
    "folder": {
      "id": 1,
      "name": "Actualités Politiques"
    },
    "creator": {
      "id": 1,
      "name": "Directeur de Publication"
    }
  }
}
```

### 2. **Endpoint de mise à jour (métadonnées uniquement)**
```http
PUT /api/articles/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "folder_id": 2,
  "assigned_to": 3,
  "status": "review"
}
```

## 🎨 **Implémentation Frontend**

### 1. **Service API - Méthode de sauvegarde**

```typescript
// services/apiService.ts
class ApiService {
  // ... autres méthodes

  async saveArticle(articleId: number, articleData: {
    title: string;
    content: string;
    folder_id?: number;
    seo_title?: string;
    seo_description?: string;
    seo_keywords?: string[];
    status?: string;
  }) {
    const response = await fetch(`${API_BASE_URL}/api/articles/${articleId}/save`, {
      method: 'POST',
      headers: this.getAuthHeaders(),
      body: JSON.stringify(articleData),
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    return response.json();
  }

  // Méthode pour mettre à jour uniquement les métadonnées
  async updateArticleMetadata(articleId: number, metadata: {
    folder_id?: number;
    assigned_to?: number;
    status?: string;
  }) {
    const response = await fetch(`${API_BASE_URL}/api/articles/${articleId}`, {
      method: 'PUT',
      headers: this.getAuthHeaders(),
      body: JSON.stringify(metadata),
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    return response.json();
  }
}
```

### 2. **Composant Editor - Sauvegarde manuelle**

```typescript
// Editor.tsx
import React, { useState, useEffect } from 'react';
import apiService from '../services/apiService';

interface Article {
  id: number;
  title: string;
  content: string;
  status: string;
  folder_id: number | null;
  seo_title?: string;
  seo_description?: string;
  seo_keywords?: string[];
}

const Editor: React.FC = () => {
  const [article, setArticle] = useState<Article>({
    id: 0,
    title: '',
    content: '',
    status: 'draft',
    folder_id: null,
    seo_title: '',
    seo_description: '',
    seo_keywords: []
  });
  
  const [isSaving, setIsSaving] = useState(false);
  const [saveMessage, setSaveMessage] = useState<string | null>(null);
  const [hasUnsavedChanges, setHasUnsavedChanges] = useState(false);

  // Détecter les changements non sauvegardés
  useEffect(() => {
    setHasUnsavedChanges(true);
  }, [article.title, article.content]);

  // Fonction de sauvegarde manuelle
  const handleSave = async () => {
    if (!article.id) {
      alert('Aucun article à sauvegarder');
      return;
    }

    setIsSaving(true);
    setSaveMessage(null);

    try {
      const response = await apiService.saveArticle(article.id, {
        title: article.title,
        content: article.content,
        folder_id: article.folder_id,
        seo_title: article.seo_title,
        seo_description: article.seo_description,
        seo_keywords: article.seo_keywords,
        status: article.status
      });

      if (response.success) {
        setSaveMessage('Article sauvegardé avec succès !');
        setHasUnsavedChanges(false);
        // Mettre à jour l'article avec les données retournées
        setArticle(response.data);
      }
    } catch (error: any) {
      console.error('Erreur lors de la sauvegarde:', error);
      setSaveMessage('Erreur lors de la sauvegarde');
    } finally {
      setIsSaving(false);
      // Effacer le message après 3 secondes
      setTimeout(() => setSaveMessage(null), 3000);
    }
  };

  // Fonction pour mettre à jour les métadonnées (sans sauvegarder le contenu)
  const updateMetadata = async (metadata: Partial<Article>) => {
    if (!article.id) return;

    try {
      await apiService.updateArticleMetadata(article.id, {
        folder_id: metadata.folder_id,
        status: metadata.status
      });
    } catch (error) {
      console.error('Erreur lors de la mise à jour des métadonnées:', error);
    }
  };

  return (
    <div className="editor">
      {/* Barre d'outils avec bouton de sauvegarde */}
      <div className="editor-toolbar">
        <button 
          onClick={handleSave}
          disabled={isSaving || !hasUnsavedChanges}
          className={`save-button ${hasUnsavedChanges ? 'has-changes' : ''}`}
        >
          {isSaving ? 'Sauvegarde...' : '💾 Sauvegarder'}
        </button>
        
        {saveMessage && (
          <div className={`save-message ${saveMessage.includes('Erreur') ? 'error' : 'success'}`}>
            {saveMessage}
          </div>
        )}
        
        {hasUnsavedChanges && (
          <div className="unsaved-indicator">
            ⚠️ Modifications non sauvegardées
          </div>
        )}
      </div>

      {/* Formulaire d'édition */}
      <div className="editor-content">
        <input
          type="text"
          value={article.title}
          onChange={(e) => setArticle({...article, title: e.target.value})}
          placeholder="Titre de l'article"
          className="article-title"
        />
        
        <textarea
          value={article.content}
          onChange={(e) => setArticle({...article, content: e.target.value})}
          placeholder="Contenu de l'article..."
          className="article-content"
        />
        
        {/* Métadonnées */}
        <div className="metadata">
          <select
            value={article.status}
            onChange={(e) => {
              setArticle({...article, status: e.target.value});
              updateMetadata({status: e.target.value});
            }}
          >
            <option value="draft">Brouillon</option>
            <option value="review">En révision</option>
            <option value="published">Publié</option>
          </select>
        </div>
      </div>
    </div>
  );
};

export default Editor;
```

### 3. **Styles CSS pour l'interface**

```css
/* Editor.css */
.editor-toolbar {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 10px;
  background: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
}

.save-button {
  background: #007bff;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
}

.save-button:disabled {
  background: #6c757d;
  cursor: not-allowed;
}

.save-button.has-changes {
  background: #28a745;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.7; }
  100% { opacity: 1; }
}

.save-message {
  padding: 5px 10px;
  border-radius: 4px;
  font-size: 14px;
}

.save-message.success {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.save-message.error {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.unsaved-indicator {
  color: #856404;
  background: #fff3cd;
  padding: 5px 10px;
  border-radius: 4px;
  font-size: 12px;
  border: 1px solid #ffeaa7;
}

.article-title {
  width: 100%;
  font-size: 24px;
  font-weight: bold;
  padding: 15px;
  border: none;
  border-bottom: 2px solid #dee2e6;
  outline: none;
}

.article-content {
  width: 100%;
  min-height: 400px;
  padding: 15px;
  border: none;
  outline: none;
  font-family: inherit;
  resize: vertical;
}

.metadata {
  padding: 15px;
  background: #f8f9fa;
  border-top: 1px solid #dee2e6;
}
```

## 🚫 **Suppression de l'enregistrement automatique**

### 1. **Supprimer les timers/useEffect d'auto-save**

```typescript
// ❌ SUPPRIMER ce code
useEffect(() => {
  const autoSave = setInterval(() => {
    if (article.id && hasUnsavedChanges) {
      handleSave();
    }
  }, 30000); // Sauvegarde automatique toutes les 30 secondes

  return () => clearInterval(autoSave);
}, [article.id, hasUnsavedChanges]);

// ❌ SUPPRIMER aussi les debounce timers
const debouncedSave = useCallback(
  debounce(() => {
    if (article.id) {
      handleSave();
    }
  }, 2000),
  [article.id]
);
```

### 2. **Garder seulement la sauvegarde manuelle**

```typescript
// ✅ GARDER seulement ceci
const handleSave = async () => {
  // Logique de sauvegarde manuelle uniquement
};
```

## 📋 **Résumé des changements**

1. ✅ **Nouvel endpoint** : `POST /api/articles/{id}/save`
2. ✅ **Sauvegarde manuelle** : Bouton "Sauvegarder" uniquement
3. ✅ **Suppression auto-save** : Plus d'enregistrement automatique
4. ✅ **Indicateur de changements** : Avertissement si modifications non sauvegardées
5. ✅ **Feedback visuel** : Message de confirmation/erreur
6. ✅ **Mise à jour du slug** : Automatique lors du changement de titre

## 🎯 **Avantages**

- **Contrôle total** : L'utilisateur décide quand sauvegarder
- **Performance** : Pas de requêtes automatiques inutiles
- **Clarté** : Indication claire des modifications non sauvegardées
- **Fiabilité** : Une seule sauvegarde par clic sur le bouton
