# 🔧 Guide de Correction des Notifications

## ✅ **Problèmes Résolus**

### **1. Erreur 422 - "The user id field is required"**
- ✅ **Corrigé** : Le champ `user_id` est maintenant optionnel
- ✅ **Auto-assignation** : Si non fourni, utilise l'utilisateur authentifié

### **2. Nouvelle route spécialisée pour le workflow**
- ✅ **Route ajoutée** : `POST /api/notifications/workflow`
- ✅ **Envoi par rôle** : Peut envoyer à tous les utilisateurs d'un rôle spécifique

## 🚀 **Nouvelles Fonctionnalités**

### **Route Standard** : `POST /api/notifications`
```json
{
  "title": "Titre de la notification",
  "message": "Message de la notification",
  "type": "article_review_request",
  "data": {"article_id": 123},
  "user_id": 456  // Optionnel - utilise l'utilisateur connecté si non fourni
}
```

### **Route Workflow** : `POST /api/notifications/workflow`
```json
{
  "type": "article_review_request",
  "title": "Nouvel article à réviser",
  "message": "Un nouvel article vous a été assigné pour révision",
  "data": {"article_id": 123},
  "recipient_role": "secretaire_redaction"  // Envoie à tous les secrétaires
}
```

## 🎯 **Types de Notifications Supportés**

- `article_review_request` - Demande de révision
- `article_reviewed` - Article révisé
- `article_approved` - Article approuvé
- `article_rejected` - Article rejeté
- `article_published` - Article publié

## 👥 **Rôles de Destinataires**

- `secretaire_redaction` - Secrétaire de rédaction
- `directeur_publication` - Directeur de publication
- `journaliste` - Journaliste

## 💡 **Exemples d'Utilisation Frontend**

### **Notification Simple**
```javascript
// Envoyer une notification à l'utilisateur connecté
await apiService.post('/notifications', {
  title: "Article sauvegardé",
  message: "Votre article a été sauvegardé avec succès",
  type: "article_saved"
});
```

### **Notification de Workflow**
```javascript
// Envoyer une notification à tous les secrétaires
await apiService.post('/notifications/workflow', {
  type: "article_review_request",
  title: "Nouvel article à réviser",
  message: `L'article "${articleTitle}" vous a été assigné pour révision`,
  data: { article_id: articleId },
  recipient_role: "secretaire_redaction"
});
```

## 🔄 **Migration Frontend**

### **Ancien Code (à remplacer)**
```javascript
// ❌ Ancien - causait l'erreur 422
await apiService.post('/notifications', {
  user_id: userId,  // Causait l'erreur
  title: "Titre",
  message: "Message"
});
```

### **Nouveau Code (recommandé)**
```javascript
// ✅ Nouveau - fonctionne parfaitement
await apiService.post('/notifications', {
  title: "Titre",
  message: "Message",
  type: "article_review_request"
  // user_id automatiquement assigné à l'utilisateur connecté
});
```

## 🎊 **Résultat**

- ✅ **Plus d'erreur 422** sur les notifications
- ✅ **Envoi automatique** par rôle
- ✅ **API simplifiée** pour le frontend
- ✅ **Notifications de workflow** intégrées

Votre système de notifications est maintenant **100% fonctionnel** ! 🚀

