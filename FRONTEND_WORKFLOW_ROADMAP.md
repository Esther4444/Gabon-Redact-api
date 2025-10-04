# 🚀 Feuille de route Frontend - Système de Workflow et Messagerie

## 🎯 Vue d'ensemble du système implémenté

### **Workflow de rédaction complet :**
1. **Journaliste** → Crée un article (statut: "En cours de rédaction")
2. **Journaliste** → Soumet au Secrétaire de rédaction (statut: "Soumis pour révision")
3. **Secrétaire** → Révisé et envoie au Directeur (statut: "En révision")
4. **Directeur** → Approuve/Rejette/Publie (statut: "Approuvé"/"Rejeté"/"Publié")

### **Système de messagerie interne :**
- Messages entre utilisateurs
- Notifications automatiques du workflow
- Conversations groupées
- Messages liés aux articles

## 📊 Nouvelles structures de données

### **Article avec workflow :**
```typescript
interface Article {
  id: number;
  title: string;
  content: string;
  status: 'draft' | 'published' | 'review';
  workflow_status: 'draft' | 'submitted' | 'in_review' | 'approved' | 'rejected' | 'published';
  current_reviewer_id: number | null;
  submitted_at: string | null;
  reviewed_at: string | null;
  approved_at: string | null;
  rejection_reason: string | null;
  workflow_history: any[];
  creator: User;
  current_reviewer: User | null;
  workflow_steps: ArticleWorkflow[];
  // ... autres champs existants
}
```

### **Workflow Step :**
```typescript
interface ArticleWorkflow {
  id: number;
  article_id: number;
  from_user_id: number | null;
  to_user_id: number;
  action: 'submitted' | 'reviewed' | 'approved' | 'rejected' | 'published';
  status: 'pending' | 'completed' | 'rejected';
  comment: string | null;
  action_at: string | null;
  from_user: User | null;
  to_user: User;
}
```

### **Message :**
```typescript
interface Message {
  id: number;
  sender_id: number;
  recipient_id: number;
  subject: string;
  body: string;
  is_read: boolean;
  article_id: number | null;
  parent_message_id: number | null;
  attachments: any[] | null;
  read_at: string | null;
  sender: User;
  recipient: User;
  article: Article | null;
  replies: Message[];
}
```

## 🔌 Nouvelles routes API

### **Workflow :**
```typescript
// Soumettre un article pour révision
POST /api/articles/{id}/submit-review
{
  "reviewer_id": number,
  "comment": string
}

// Réviser un article (Secrétaire)
POST /api/articles/{id}/review
{
  "comment": string
}

// Approuver un article (Directeur)
POST /api/articles/{id}/approve
{
  "comment": string
}

// Rejeter un article (Directeur)
POST /api/articles/{id}/reject
{
  "reason": string,
  "comment": string
}

// Publier un article (Directeur)
POST /api/articles/{id}/publish

// Articles en attente pour l'utilisateur
GET /api/workflow/pending-articles

// Historique du workflow d'un article
GET /api/articles/{id}/workflow-history

// Statistiques du workflow
GET /api/workflow/stats
```

### **Messagerie :**
```typescript
// Lister les messages
GET /api/messages?unread_only=true&article_id=123

// Envoyer un message
POST /api/messages
{
  "recipient_id": number,
  "subject": string,
  "body": string,
  "article_id": number | null,
  "parent_message_id": number | null
}

// Répondre à un message
POST /api/messages/{id}/reply
{
  "body": string
}

// Marquer comme lu/non lu
PATCH /api/messages/{id}/read
PATCH /api/messages/{id}/unread

// Compter les messages non lus
GET /api/messages/unread/count

// Conversations groupées
GET /api/conversations
```

## 🎨 Composants React à créer

### **1. Dashboard de workflow**

```typescript
// components/WorkflowDashboard.tsx
interface WorkflowDashboardProps {
  userRole: 'journaliste' | 'secretaire_redaction' | 'directeur_publication';
}

const WorkflowDashboard: React.FC<WorkflowDashboardProps> = ({ userRole }) => {
  const [stats, setStats] = useState(null);
  const [pendingArticles, setPendingArticles] = useState([]);

  return (
    <div className="workflow-dashboard">
      <div className="stats-grid">
        <StatCard title="Mes articles" data={stats?.my_articles} />
        <StatCard title="En attente de révision" count={stats?.pending_review} />
      </div>
      
      {userRole !== 'journaliste' && (
        <PendingArticlesList articles={pendingArticles} />
      )}
    </div>
  );
};
```

### **2. Liste des articles avec workflow**

```typescript
// components/ArticlesWorkflowTable.tsx
const ArticlesWorkflowTable: React.FC = () => {
  const [articles, setArticles] = useState([]);
  const [workflowFilter, setWorkflowFilter] = useState('all');

  const getWorkflowStatusColor = (status: string) => {
    const colors = {
      'draft': 'bg-gray-100 text-gray-800',
      'submitted': 'bg-yellow-100 text-yellow-800',
      'in_review': 'bg-blue-100 text-blue-800',
      'approved': 'bg-green-100 text-green-800',
      'rejected': 'bg-red-100 text-red-800',
      'published': 'bg-purple-100 text-purple-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
  };

  const getWorkflowActions = (article: Article) => {
    const userRole = getUserRole();
    const actions = [];

    if (userRole === 'journaliste' && article.workflow_status === 'draft') {
      actions.push(
        <button onClick={() => submitForReview(article)}>
          Soumettre pour révision
        </button>
      );
    }

    if (userRole === 'secretaire_redaction' && article.workflow_status === 'submitted') {
      actions.push(
        <button onClick={() => reviewArticle(article)}>
          Réviser
        </button>
      );
    }

    if (userRole === 'directeur_publication' && article.workflow_status === 'in_review') {
      actions.push(
        <button onClick={() => approveArticle(article)}>
          Approuver
        </button>,
        <button onClick={() => rejectArticle(article)}>
          Rejeter
        </button>
      );
    }

    if (userRole === 'directeur_publication' && article.workflow_status === 'approved') {
      actions.push(
        <button onClick={() => publishArticle(article)}>
          Publier
        </button>
      );
    }

    return actions;
  };

  return (
    <div className="articles-workflow-table">
      <div className="filters">
        <select value={workflowFilter} onChange={(e) => setWorkflowFilter(e.target.value)}>
          <option value="all">Tous les statuts</option>
          <option value="draft">En cours de rédaction</option>
          <option value="submitted">Soumis pour révision</option>
          <option value="in_review">En révision</option>
          <option value="approved">Approuvé</option>
          <option value="rejected">Rejeté</option>
          <option value="published">Publié</option>
        </select>
      </div>

      <table>
        <thead>
          <tr>
            <th>Titre</th>
            <th>Statut Workflow</th>
            <th>Réviseur actuel</th>
            <th>Date de soumission</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          {articles.map(article => (
            <tr key={article.id}>
              <td>{article.title}</td>
              <td>
                <span className={`status-badge ${getWorkflowStatusColor(article.workflow_status)}`}>
                  {Article.WORKFLOW_STATUSES[article.workflow_status]}
                </span>
              </td>
              <td>{article.current_reviewer?.name || 'Aucun'}</td>
              <td>{article.submitted_at ? formatDate(article.submitted_at) : '-'}</td>
              <td>
                <div className="actions">
                  {getWorkflowActions(article)}
                  <button onClick={() => viewWorkflowHistory(article)}>
                    Historique
                  </button>
                </div>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
};
```

### **3. Interface de messagerie**

```typescript
// components/MessagingInterface.tsx
const MessagingInterface: React.FC = () => {
  const [messages, setMessages] = useState([]);
  const [conversations, setConversations] = useState([]);
  const [selectedConversation, setSelectedConversation] = useState(null);
  const [unreadCount, setUnreadCount] = useState(0);

  return (
    <div className="messaging-interface">
      <div className="sidebar">
        <div className="unread-count">
          {unreadCount} messages non lus
        </div>
        
        <div className="conversations-list">
          {conversations.map(conversation => (
            <div 
              key={conversation.correspondent.id}
              className={`conversation-item ${conversation.unread_count > 0 ? 'unread' : ''}`}
              onClick={() => setSelectedConversation(conversation)}
            >
              <img src={conversation.correspondent.profile.avatar_url} />
              <div>
                <h4>{conversation.correspondent.name}</h4>
                <p>{conversation.latest_message.subject}</p>
                {conversation.unread_count > 0 && (
                  <span className="unread-badge">{conversation.unread_count}</span>
                )}
              </div>
            </div>
          ))}
        </div>
      </div>

      <div className="message-thread">
        {selectedConversation && (
          <MessageThread conversation={selectedConversation} />
        )}
      </div>
    </div>
  );
};
```

### **4. Composant de thread de messages**

```typescript
// components/MessageThread.tsx
const MessageThread: React.FC<{ conversation: any }> = ({ conversation }) => {
  const [messages, setMessages] = useState([]);
  const [newMessage, setNewMessage] = useState('');

  const sendMessage = async () => {
    if (!newMessage.trim()) return;

    await api.post(`/messages/${conversation.latest_message.id}/reply`, {
      body: newMessage
    });

    setNewMessage('');
    loadMessages();
  };

  return (
    <div className="message-thread">
      <div className="thread-header">
        <h3>Conversation avec {conversation.correspondent.name}</h3>
      </div>

      <div className="messages-list">
        {messages.map(message => (
          <div key={message.id} className={`message ${message.sender_id === currentUserId ? 'sent' : 'received'}`}>
            <div className="message-header">
              <span>{message.sender.name}</span>
              <span>{formatDate(message.created_at)}</span>
            </div>
            <div className="message-body">
              {message.body}
            </div>
            {message.article && (
              <div className="message-article">
                Article: {message.article.title}
              </div>
            )}
          </div>
        ))}
      </div>

      <div className="message-composer">
        <textarea
          value={newMessage}
          onChange={(e) => setNewMessage(e.target.value)}
          placeholder="Tapez votre message..."
        />
        <button onClick={sendMessage}>Envoyer</button>
      </div>
    </div>
  );
};
```

### **5. Modal de soumission pour révision**

```typescript
// components/SubmitForReviewModal.tsx
const SubmitForReviewModal: React.FC<{ article: Article; onClose: () => void }> = ({ article, onClose }) => {
  const [reviewerId, setReviewerId] = useState('');
  const [comment, setComment] = useState('');
  const [reviewers, setReviewers] = useState([]);

  const submitForReview = async () => {
    await api.post(`/articles/${article.id}/submit-review`, {
      reviewer_id: reviewerId,
      comment: comment
    });

    onClose();
    // Rafraîchir la liste des articles
  };

  return (
    <div className="modal-overlay">
      <div className="modal">
        <h3>Soumettre pour révision</h3>
        
        <div className="form-group">
          <label>Secrétaire de rédaction</label>
          <select value={reviewerId} onChange={(e) => setReviewerId(e.target.value)}>
            <option value="">Sélectionner un réviseur</option>
            {reviewers.map(reviewer => (
              <option key={reviewer.id} value={reviewer.id}>
                {reviewer.name}
              </option>
            ))}
          </select>
        </div>

        <div className="form-group">
          <label>Commentaire (optionnel)</label>
          <textarea
            value={comment}
            onChange={(e) => setComment(e.target.value)}
            placeholder="Ajoutez un commentaire pour le réviseur..."
          />
        </div>

        <div className="modal-actions">
          <button onClick={onClose}>Annuler</button>
          <button onClick={submitForReview} disabled={!reviewerId}>
            Soumettre
          </button>
        </div>
      </div>
    </div>
  );
};
```

### **6. Historique du workflow**

```typescript
// components/WorkflowHistory.tsx
const WorkflowHistory: React.FC<{ article: Article }> = ({ article }) => {
  const [workflowSteps, setWorkflowSteps] = useState([]);

  return (
    <div className="workflow-history">
      <h3>Historique du workflow</h3>
      
      <div className="timeline">
        {workflowSteps.map((step, index) => (
          <div key={step.id} className="timeline-item">
            <div className="timeline-marker">
              <div className={`status-${step.status}`}></div>
            </div>
            <div className="timeline-content">
              <h4>{ArticleWorkflow.ACTIONS[step.action]}</h4>
              <p>
                De: {step.from_user?.name || 'Système'} 
                → À: {step.to_user.name}
              </p>
              {step.comment && (
                <p className="comment">{step.comment}</p>
              )}
              <span className="date">{formatDate(step.created_at)}</span>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};
```

## 🎯 Hooks personnalisés

### **1. Hook pour le workflow**

```typescript
// hooks/useWorkflow.ts
export const useWorkflow = () => {
  const [pendingArticles, setPendingArticles] = useState([]);
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(false);

  const loadPendingArticles = async () => {
    setLoading(true);
    try {
      const response = await api.get('/workflow/pending-articles');
      setPendingArticles(response.data.data);
    } finally {
      setLoading(false);
    }
  };

  const loadStats = async () => {
    const response = await api.get('/workflow/stats');
    setStats(response.data.data);
  };

  const submitForReview = async (articleId: number, reviewerId: number, comment?: string) => {
    await api.post(`/articles/${articleId}/submit-review`, {
      reviewer_id: reviewerId,
      comment
    });
    loadPendingArticles();
    loadStats();
  };

  const reviewArticle = async (articleId: number, comment?: string) => {
    await api.post(`/articles/${articleId}/review`, { comment });
    loadPendingArticles();
    loadStats();
  };

  const approveArticle = async (articleId: number, comment?: string) => {
    await api.post(`/articles/${articleId}/approve`, { comment });
    loadPendingArticles();
    loadStats();
  };

  const rejectArticle = async (articleId: number, reason: string, comment?: string) => {
    await api.post(`/articles/${articleId}/reject`, { reason, comment });
    loadPendingArticles();
    loadStats();
  };

  const publishArticle = async (articleId: number) => {
    await api.post(`/articles/${articleId}/publish`);
    loadPendingArticles();
    loadStats();
  };

  return {
    pendingArticles,
    stats,
    loading,
    loadPendingArticles,
    loadStats,
    submitForReview,
    reviewArticle,
    approveArticle,
    rejectArticle,
    publishArticle,
  };
};
```

### **2. Hook pour la messagerie**

```typescript
// hooks/useMessaging.ts
export const useMessaging = () => {
  const [messages, setMessages] = useState([]);
  const [conversations, setConversations] = useState([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [loading, setLoading] = useState(false);

  const loadMessages = async (filters = {}) => {
    setLoading(true);
    try {
      const response = await api.get('/messages', { params: filters });
      setMessages(response.data.data);
    } finally {
      setLoading(false);
    }
  };

  const loadConversations = async () => {
    const response = await api.get('/conversations');
    setConversations(response.data.data);
  };

  const loadUnreadCount = async () => {
    const response = await api.get('/messages/unread/count');
    setUnreadCount(response.data.data.unread_count);
  };

  const sendMessage = async (recipientId: number, subject: string, body: string, articleId?: number) => {
    await api.post('/messages', {
      recipient_id: recipientId,
      subject,
      body,
      article_id: articleId
    });
    loadMessages();
    loadConversations();
  };

  const replyToMessage = async (messageId: number, body: string) => {
    await api.post(`/messages/${messageId}/reply`, { body });
    loadMessages();
    loadConversations();
  };

  const markAsRead = async (messageId: number) => {
    await api.patch(`/messages/${messageId}/read`);
    loadUnreadCount();
  };

  return {
    messages,
    conversations,
    unreadCount,
    loading,
    loadMessages,
    loadConversations,
    loadUnreadCount,
    sendMessage,
    replyToMessage,
    markAsRead,
  };
};
```

## 🎨 Styles CSS recommandés

```css
/* Workflow Status Badges */
.workflow-status-badge {
  @apply inline-flex px-2 py-1 text-xs font-semibold rounded-full;
}

.workflow-status-draft { @apply bg-gray-100 text-gray-800; }
.workflow-status-submitted { @apply bg-yellow-100 text-yellow-800; }
.workflow-status-in_review { @apply bg-blue-100 text-blue-800; }
.workflow-status-approved { @apply bg-green-100 text-green-800; }
.workflow-status-rejected { @apply bg-red-100 text-red-800; }
.workflow-status-published { @apply bg-purple-100 text-purple-800; }

/* Messaging Interface */
.messaging-interface {
  @apply flex h-screen bg-gray-50;
}

.messaging-sidebar {
  @apply w-80 bg-white border-r border-gray-200 flex flex-col;
}

.conversation-item {
  @apply p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer;
}

.conversation-item.unread {
  @apply bg-blue-50 border-l-4 border-l-blue-500;
}

.unread-badge {
  @apply inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800;
}

.message-thread {
  @apply flex-1 flex flex-col;
}

.message {
  @apply mb-4 p-4 rounded-lg max-w-xs;
}

.message.sent {
  @apply bg-blue-500 text-white ml-auto;
}

.message.received {
  @apply bg-white border border-gray-200;
}

/* Workflow Timeline */
.timeline {
  @apply relative pl-8;
}

.timeline::before {
  @apply absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200;
  content: '';
}

.timeline-item {
  @apply relative mb-8;
}

.timeline-marker {
  @apply absolute -left-8 w-8 h-8 bg-white border-2 border-gray-200 rounded-full flex items-center justify-center;
}

.timeline-marker .status-completed {
  @apply w-3 h-3 bg-green-500 rounded-full;
}

.timeline-marker .status-pending {
  @apply w-3 h-3 bg-yellow-500 rounded-full;
}

.timeline-marker .status-rejected {
  @apply w-3 h-3 bg-red-500 rounded-full;
}
```

## 🚀 Implémentation par étapes

### **Phase 1 : Interface de base**
1. Créer le dashboard de workflow
2. Modifier le tableau des articles pour inclure les statuts de workflow
3. Ajouter les actions de workflow selon le rôle

### **Phase 2 : Workflow complet**
1. Implémenter la soumission pour révision
2. Interface de révision pour le secrétaire
3. Interface d'approbation/rejet pour le directeur
4. Historique du workflow

### **Phase 3 : Messagerie**
1. Interface de messagerie de base
2. Conversations groupées
3. Notifications en temps réel
4. Messages liés aux articles

### **Phase 4 : Notifications**
1. Système de notifications push
2. Notifications par email
3. Centre de notifications
4. Préférences de notification

## 📋 Checklist d'implémentation

- [ ] Créer les types TypeScript pour les nouvelles structures
- [ ] Implémenter le dashboard de workflow
- [ ] Modifier le tableau des articles avec les nouveaux statuts
- [ ] Créer les modals de workflow (soumission, révision, approbation)
- [ ] Implémenter l'historique du workflow
- [ ] Créer l'interface de messagerie
- [ ] Ajouter les notifications en temps réel
- [ ] Implémenter les hooks personnalisés
- [ ] Ajouter les styles CSS
- [ ] Tester le workflow complet
- [ ] Optimiser les performances

## 🎯 Points d'attention

1. **Gestion des rôles** : Vérifier les permissions à chaque action
2. **Notifications** : Implémenter un système de notifications en temps réel
3. **Performance** : Optimiser les requêtes pour les listes d'articles
4. **UX** : Interface intuitive pour chaque rôle
5. **Responsive** : Adapter l'interface mobile
6. **Accessibilité** : Respecter les standards d'accessibilité

Cette feuille de route vous donne tout ce qu'il faut pour implémenter le système de workflow et de messagerie complet ! 🚀
