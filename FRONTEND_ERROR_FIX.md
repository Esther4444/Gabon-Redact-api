# Correction de l'erreur "api is not defined" - Frontend

## 🔍 **Diagnostic du problème**

L'erreur `ReferenceError: api is not defined` dans votre composant `Folders.tsx` indique que la variable `api` n'est pas correctement importée ou définie.

## ✅ **Solutions à appliquer**

### 1. **Vérifier l'import du service API**

Dans votre fichier `Folders.tsx`, assurez-vous d'avoir l'import correct :

```typescript
// ✅ CORRECT
import apiService from '../services/apiService';

// ❌ INCORRECT - si vous utilisez 'api' au lieu de 'apiService'
import api from '../services/apiService';
```

### 2. **Utiliser le bon nom de variable**

```typescript
// ✅ CORRECT
const fetchArticles = async () => {
  try {
    const response = await apiService.get('/articles');
    setArticles(response.data);
  } catch (error) {
    console.error('Erreur lors du chargement des articles:', error);
  }
};

// ❌ INCORRECT
const fetchArticles = async () => {
  try {
    const response = await api.get('/articles'); // 'api' n'est pas défini
    setArticles(response.data);
  } catch (error) {
    console.error('Erreur lors du chargement des articles:', error);
  }
};
```

### 3. **Structure complète du composant Folders.tsx**

```typescript
import React, { useState, useEffect } from 'react';
import apiService from '../services/apiService'; // Import correct

interface Article {
  id: number;
  title: string;
  content: string;
  status: string;
  folder_id: number | null;
  folder?: {
    id: number;
    name: string;
  };
}

interface Folder {
  id: number;
  name: string;
  owner_id: number;
}

const Folders: React.FC = () => {
  const [folders, setFolders] = useState<Folder[]>([]);
  const [articles, setArticles] = useState<Article[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const fetchFolders = async () => {
    try {
      setLoading(true);
      const response = await apiService.get('/folders'); // Utilisez apiService
      setFolders(response.data);
    } catch (error: any) {
      console.error('Erreur lors du chargement des dossiers:', error);
      setError('Erreur lors du chargement des dossiers');
    } finally {
      setLoading(false);
    }
  };

  const fetchArticles = async () => {
    try {
      setLoading(true);
      const response = await apiService.get('/articles'); // Utilisez apiService
      setArticles(response.data);
    } catch (error: any) {
      console.error('Erreur lors du chargement des articles:', error);
      setError('Erreur lors du chargement des articles');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchFolders();
    fetchArticles();
  }, []);

  if (loading) return <div>Chargement...</div>;
  if (error) return <div>Erreur: {error}</div>;

  return (
    <div>
      <h2>Dossiers</h2>
      {folders.map(folder => (
        <div key={folder.id}>
          {folder.name}
        </div>
      ))}
      
      <h2>Articles</h2>
      {articles.map(article => (
        <div key={article.id}>
          {article.title} - {article.status}
        </div>
      ))}
    </div>
  );
};

export default Folders;
```

### 4. **Vérifier le service API**

Assurez-vous que votre `apiService` est correctement configuré :

```typescript
// services/apiService.ts
const API_BASE_URL = 'http://localhost:8000/api';

class ApiService {
  private getAuthHeaders() {
    const token = localStorage.getItem('authToken');
    return {
      'Content-Type': 'application/json',
      'Authorization': token ? `Bearer ${token}` : '',
    };
  }

  async get(endpoint: string) {
    const response = await fetch(`${API_BASE_URL}${endpoint}`, {
      method: 'GET',
      headers: this.getAuthHeaders(),
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    return response.json();
  }

  async post(endpoint: string, data: any) {
    const response = await fetch(`${API_BASE_URL}${endpoint}`, {
      method: 'POST',
      headers: this.getAuthHeaders(),
      body: JSON.stringify(data),
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    return response.json();
  }
}

export default new ApiService();
```

### 5. **Debug rapide**

Ajoutez ces lignes temporaires pour vérifier :

```typescript
// En haut de votre composant Folders.tsx
console.log('apiService:', apiService);
console.log('typeof apiService:', typeof apiService);

// Dans votre fonction fetchArticles
const fetchArticles = async () => {
  console.log('apiService dans fetchArticles:', apiService); // Debug
  try {
    const response = await apiService.get('/articles');
    setArticles(response.data);
  } catch (error: any) {
    console.error('Erreur lors du chargement des articles:', error);
  }
};
```

## 🔧 **Points à vérifier**

1. ✅ **Import correct** : `import apiService from '../services/apiService';`
2. ✅ **Nom de variable** : Utilisez `apiService` et non `api`
3. ✅ **Chemin d'import** : Vérifiez que le chemin vers `apiService` est correct
4. ✅ **Export du service** : Assurez-vous que `apiService` est bien exporté
5. ✅ **Token d'authentification** : Vérifiez que le token est stocké dans localStorage

## 🎯 **Résultat attendu**

Après ces corrections, votre composant `Folders.tsx` devrait :
- ✅ Se charger sans erreur `api is not defined`
- ✅ Récupérer les dossiers depuis l'API
- ✅ Récupérer les articles depuis l'API
- ✅ Afficher les données correctement

## 📝 **Note importante**

L'API fonctionne parfaitement (testée et vérifiée). Le problème est uniquement côté frontend dans l'import ou l'utilisation du service API.
