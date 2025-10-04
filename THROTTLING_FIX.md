# 🔧 Solution pour l'erreur "Too Many Attempts" (429)

## 🚨 Problème identifié

Le frontend fait trop de requêtes simultanées vers l'API, déclenchant la protection anti-spam de Laravel.

## ✅ Solutions appliquées

### 1. **Throttling désactivé temporairement** ✅
- Le middleware `ThrottleRequests` a été commenté dans `app/Http/Kernel.php`
- Cela permet le développement sans limitation de taux

### 2. **Solutions pour le frontend**

#### A. Ajouter un délai entre les requêtes
```typescript
// Dans useArticles.ts - Ajouter un debounce
import { useCallback } from 'react';
import { useDebouncedCallback } from 'use-debounce';

const debouncedLoadArticles = useDebouncedCallback(
  async () => {
    // Votre logique de chargement
  },
  500 // Attendre 500ms avant de faire la requête
);
```

#### B. Éviter les requêtes multiples
```typescript
// Dans useArticles.ts - Ajouter un état de chargement
const [isLoading, setIsLoading] = useState(false);

const loadArticles = async () => {
  if (isLoading) return; // Éviter les requêtes multiples
  
  setIsLoading(true);
  try {
    // Votre logique de chargement
  } finally {
    setIsLoading(false);
  }
};
```

#### C. Utiliser un cache simple
```typescript
// Dans useArticles.ts - Ajouter un cache
const [cache, setCache] = useState<Map<string, any>>(new Map());

const loadArticles = async (params: any) => {
  const cacheKey = JSON.stringify(params);
  
  if (cache.has(cacheKey)) {
    setArticles(cache.get(cacheKey));
    return;
  }
  
  // Faire la requête et mettre en cache
  const data = await fetchArticles(params);
  setCache(prev => new Map(prev).set(cacheKey, data));
};
```

## 🔄 Solutions alternatives

### Option 1 : Réactiver le throttling avec des limites plus permissives

```php
// Dans app/Http/Kernel.php
'api' => [
    \Illuminate\Routing\Middleware\ThrottleRequests::class.':60,1', // 60 requêtes par minute
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

### Option 2 : Throttling personnalisé par route

```php
// Dans routes/api.php
Route::middleware(['auth:sanctum', 'throttle:100,1'])->group(function () {
    Route::get('articles', [ArticleController::class, 'index']);
});
```

### Option 3 : Configuration dans .env

```env
# Dans .env
THROTTLE_REQUESTS=100
THROTTLE_MINUTES=1
```

## 🎯 Recommandations pour le frontend

### 1. **Implémenter un debounce**
```typescript
import { useDebouncedCallback } from 'use-debounce';

const debouncedSearch = useDebouncedCallback(
  (searchTerm: string) => {
    loadArticles({ search: searchTerm });
  },
  300 // 300ms de délai
);
```

### 2. **Ajouter un état de chargement global**
```typescript
const [loadingStates, setLoadingStates] = useState({
  articles: false,
  preview: false,
  save: false
});
```

### 3. **Implémenter un retry avec backoff**
```typescript
const retryWithBackoff = async (fn: () => Promise<any>, retries = 3) => {
  for (let i = 0; i < retries; i++) {
    try {
      return await fn();
    } catch (error) {
      if (error.message.includes('Too Many Attempts') && i < retries - 1) {
        await new Promise(resolve => setTimeout(resolve, Math.pow(2, i) * 1000));
        continue;
      }
      throw error;
    }
  }
};
```

## 🚀 Test de la solution

1. **Redémarrer le serveur Laravel**
```bash
php artisan serve
```

2. **Vider le cache de throttling**
```bash
php artisan cache:clear
php artisan config:clear
```

3. **Tester depuis le frontend**
- Les requêtes devraient maintenant passer sans erreur 429
- Vérifier que la pagination fonctionne
- Tester la prévisualisation

## ⚠️ Important pour la production

**Avant de déployer en production :**
1. Réactiver le throttling avec des limites appropriées
2. Implémenter le debounce côté frontend
3. Ajouter une gestion d'erreur pour les cas de throttling
4. Tester avec un volume de requêtes réaliste

## 🔍 Debugging

Pour surveiller les requêtes :
```bash
# Voir les logs en temps réel
tail -f storage/logs/laravel.log

# Voir les requêtes SQL
# Ajouter dans .env : DB_LOG_QUERIES=true
```

La solution temporaire devrait résoudre le problème immédiatement !
