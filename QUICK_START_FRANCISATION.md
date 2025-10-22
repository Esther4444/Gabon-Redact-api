# ⚡ Guide Rapide : Francisation en 5 Étapes

## 🎯 Ce que nous avons fait

✅ Migration complète créée (`2025_10_07_200808_renommer_tables_et_colonnes_en_francais.php`)  
✅ Guide de francisation détaillé créé  
✅ Exemples de modèles francisés créés  

---

## 🚀 Étapes à Suivre

### **Étape 1 : Installer la Dépendance Requise**

```bash
cd RedacGabonProApi
composer require doctrine/dbal
```

**⚠️ Cette étape est OBLIGATOIRE** - Laravel a besoin de `doctrine/dbal` pour renommer les colonnes.

---

### **Étape 2 : Sauvegarder Votre Base de Données**

```bash
# Avec mysqldump
mysqldump -u root -p nom_de_votre_base > backup_avant_francisation.sql

# Ou utilisez phpMyAdmin / Adminer
```

---

### **Étape 3 : Tester la Migration**

```bash
# Vérifier l'état actuel
php artisan migrate:status

# Exécuter la migration
php artisan migrate

# Si problème, revenir en arrière
php artisan migrate:rollback
```

---

### **Étape 4 : Mettre à Jour les Modèles**

Référez-vous au fichier `MODELES_FRANCISES_EXEMPLES.md` pour des exemples complets.

**Points clés à changer dans chaque modèle :**

1. Nom de la table
2. Noms des colonnes dans `$fillable`
3. Constantes `CREATED_AT`, `UPDATED_AT`, `DELETED_AT`
4. Noms des colonnes dans les relations
5. Noms des colonnes dans les scopes

**Exemple minimal :**

```php
class Article extends Model
{
    protected $table = 'articles';
    
    const CREATED_AT = 'cree_le';
    const UPDATED_AT = 'modifie_le';
    const DELETED_AT = 'supprime_le';
    
    protected $fillable = [
        'titre',        // au lieu de 'title'
        'contenu',      // au lieu de 'content'
        'statut',       // au lieu de 'status'
        // etc...
    ];
    
    public function createur()
    {
        return $this->belongsTo(User::class, 'cree_par');
    }
}
```

---

### **Étape 5 : Mettre à Jour les Contrôleurs**

Cherchez et remplacez les noms de colonnes dans tous vos contrôleurs :

```php
// AVANT
$article = Article::create([
    'title' => $request->title,
    'content' => $request->content,
    'status' => 'draft',
    'created_by' => auth()->id(),
]);

// APRÈS
$article = Article::create([
    'titre' => $request->titre,
    'contenu' => $request->contenu,
    'statut' => 'draft',
    'cree_par' => auth()->id(),
]);
```

---

## 📋 Checklist Complète

### Backend (Laravel)

- [ ] ✅ Installer `doctrine/dbal`
- [ ] ✅ Sauvegarder la base de données
- [ ] ✅ Exécuter `php artisan migrate`
- [ ] ⬜ Mettre à jour `app/Models/User.php`
- [ ] ⬜ Mettre à jour `app/Models/Article.php`
- [ ] ⬜ Mettre à jour `app/Models/Profile.php`
- [ ] ⬜ Mettre à jour `app/Models/Folder.php`
- [ ] ⬜ Mettre à jour `app/Models/Comment.php`
- [ ] ⬜ Mettre à jour `app/Models/Message.php`
- [ ] ⬜ Mettre à jour `app/Models/Notification.php`
- [ ] ⬜ Mettre à jour `app/Models/Media.php`
- [ ] ⬜ Mettre à jour tous les autres modèles
- [ ] ⬜ Mettre à jour tous les contrôleurs
- [ ] ⬜ Mettre à jour les seeders
- [ ] ⬜ Mettre à jour les factories
- [ ] ⬜ Tester toutes les routes API

### Frontend (React)

- [ ] ⬜ Mettre à jour les interfaces TypeScript
- [ ] ⬜ Mettre à jour les appels API
- [ ] ⬜ Tester toutes les fonctionnalités

---

## 🧪 Tests à Effectuer

Après la migration, testez :

1. **Authentification**
   - [ ] Connexion
   - [ ] Déconnexion
   - [ ] Inscription
   - [ ] Réinitialisation mot de passe

2. **Articles**
   - [ ] Créer un article
   - [ ] Modifier un article
   - [ ] Supprimer un article
   - [ ] Lister les articles

3. **Workflow**
   - [ ] Soumettre pour révision
   - [ ] Réviser un article
   - [ ] Approuver un article
   - [ ] Rejeter un article
   - [ ] Publier un article

4. **Messages**
   - [ ] Envoyer un message
   - [ ] Lire un message
   - [ ] Répondre à un message

5. **Notifications**
   - [ ] Recevoir une notification
   - [ ] Marquer comme lu

6. **Médias**
   - [ ] Upload d'image
   - [ ] Liste des médias
   - [ ] Suppression

---

## 🆘 Résolution de Problèmes

### Erreur : "doctrine/dbal not found"

```bash
composer require doctrine/dbal
```

### Erreur : "Column not found" après migration

Vérifiez que vous avez bien mis à jour les noms de colonnes dans vos modèles.

### Erreur : "SQLSTATE[42S22]: Column not found"

Cherchez dans vos contrôleurs les anciennes références aux colonnes :

```bash
# Trouver toutes les références à 'created_by'
grep -r "created_by" app/Http/Controllers/

# Trouver toutes les références à 'user_id'
grep -r "user_id" app/Http/Controllers/
```

### La migration échoue

```bash
# Revenir en arrière
php artisan migrate:rollback

# Restaurer la sauvegarde
mysql -u root -p nom_de_votre_base < backup_avant_francisation.sql
```

---

## 📚 Documents de Référence

1. **GUIDE_FRANCISATION.md** : Guide complet et détaillé
2. **MODELES_FRANCISES_EXEMPLES.md** : Exemples de modèles mis à jour
3. **Migration** : `database/migrations/2025_10_07_200808_renommer_tables_et_colonnes_en_francais.php`

---

## 🎯 Tableau de Correspondance Rapide

| Avant | Après |
|-------|-------|
| `users` | `utilisateurs` |
| `name` | `nom` |
| `password` | `mot_de_passe` |
| `created_at` | `cree_le` |
| `updated_at` | `modifie_le` |
| `deleted_at` | `supprime_le` |
| `user_id` | `utilisateur_id` |
| `title` | `titre` |
| `content` | `contenu` |
| `status` | `statut` |
| `folder_id` | `dossier_id` |
| `created_by` | `cree_par` |
| `assigned_to` | `assigne_a` |
| `published_at` | `publie_le` |
| `author_id` | `auteur_id` |
| `sender_id` | `expediteur_id` |
| `recipient_id` | `destinataire_id` |
| `subject` | `sujet` |
| `body` | `contenu` |
| `is_read` | `est_lu` |
| `read_at` | `lu_le` |

---

## ✅ Ordre Recommandé de Mise à Jour

1. **Database** : Exécuter la migration
2. **Modèles** : Commencer par User, puis Article, puis les autres
3. **Controllers** : Un par un, en testant après chaque modification
4. **Services** : Si vous avez des classes de service
5. **Seeders/Factories** : Pour pouvoir tester avec des données
6. **Frontend** : Une fois le backend stable
7. **Tests** : Mettre à jour les tests automatisés
8. **Documentation** : Mettre à jour la documentation API

---

## 🎉 Après la Francisation

Votre base de données sera entièrement en français ! Cela facilitera :

- ✅ La compréhension du code
- ✅ La maintenance
- ✅ La collaboration avec des développeurs francophones
- ✅ La documentation en français
- ✅ Les requêtes SQL plus lisibles

---

**Besoin d'aide ?** Référez-vous aux guides détaillés ou contactez l'équipe de développement.

**Version** : 1.0  
**Date** : 8 octobre 2025


