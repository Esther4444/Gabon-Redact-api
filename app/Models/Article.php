<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
	use HasFactory, SoftDeletes;

	protected $fillable = [
		'titre','slug','contenu','statut','statut_workflow','dossier_id','created_by','assigned_to','current_reviewer_id',
		'titre_seo','description_seo','mots_cles_seo','publie_le','soumis_le','relu_le','approuve_le',
		'raison_rejet','historique_workflow','metadonnees',
		// Nouveaux champs
		'category','tags','featured_image','excerpt','reading_time','word_count','character_count',
		'author_bio','custom_css','custom_js','template','language','is_featured','is_breaking_news',
		'allow_comments','social_media_data'
	];

	protected $casts = [
		'publie_le' => 'datetime',
		'soumis_le' => 'datetime',
		'relu_le' => 'datetime',
		'approuve_le' => 'datetime',
		'mots_cles_seo' => 'array',
		'historique_workflow' => 'array',
		'metadonnees' => 'array',
		// Nouveaux casts
		'tags' => 'array',
		'social_media_data' => 'array',
		'is_featured' => 'boolean',
		'is_breaking_news' => 'boolean',
		'allow_comments' => 'boolean',
		'reading_time' => 'integer',
		'word_count' => 'integer',
		'character_count' => 'integer',
	];

	public function folder()
	{
		return $this->belongsTo(Folder::class, 'dossier_id');
	}

	public function creator()
	{
		return $this->belongsTo(User::class, 'created_by');
	}

	public function assignee()
	{
		return $this->belongsTo(User::class, 'assigned_to');
	}

	public function comments()
	{
		return $this->hasMany(Comment::class);
	}

	public function schedules()
	{
		return $this->hasMany(PublicationSchedule::class);
	}

	public function currentReviewer()
	{
		return $this->belongsTo(User::class, 'current_reviewer_id');
	}

	public function workflowSteps()
	{
		return $this->hasMany(ArticleWorkflow::class);
	}

	public function messages()
	{
		return $this->hasMany(Message::class);
	}

	// Scopes pour le workflow
	public function scopeByWorkflowStatus($query, $status)
	{
		return $query->where('statut_workflow', $status);
	}

	public function scopeForReviewer($query, $userId)
	{
		return $query->where('current_reviewer_id', $userId);
	}

	public function scopeSubmitted($query)
	{
		return $query->where('statut_workflow', 'submitted');
	}

	public function scopeInReview($query)
	{
		return $query->where('statut_workflow', 'in_review');
	}

	public function scopeApproved($query)
	{
		return $query->where('statut_workflow', 'approved');
	}

	public function scopeRejected($query)
	{
		return $query->where('statut_workflow', 'rejected');
	}

	// Constantes pour les statuts de workflow
	const WORKFLOW_STATUSES = [
		'draft' => 'En cours de rédaction',
		'submitted' => 'Soumis pour révision',
		'in_review' => 'En révision',
		'approved' => 'Approuvé',
		'rejected' => 'Rejeté',
		'published' => 'Publié',
	];

	// Méthodes pour le workflow
	public function submitForReview($reviewerId, $comment = null)
	{
		$this->update([
			'statut_workflow' => 'submitted',
			'current_reviewer_id' => $reviewerId,
			'soumis_le' => now(),
		]);

		// Créer une étape de workflow
		ArticleWorkflow::create([
			'article_id' => $this->id,
			'from_user_id' => $this->created_by,
			'to_user_id' => $reviewerId,
			'action' => 'submitted',
			'statut' => 'pending',
			'commentaire' => $comment,
		]);

		// Envoyer une notification
		$this->sendNotification($reviewerId, 'Nouvel article soumis pour révision', $this->titre);
	}

	public function review($comment = null)
	{
		$this->update([
			'statut_workflow' => 'in_review',
			'relu_le' => now(),
		]);

		// Mettre à jour l'étape de workflow
		$workflow = $this->workflowSteps()->where('statut', 'pending')->first();
		if ($workflow) {
			$workflow->update([
				'statut' => 'completed',
				'action' => 'reviewed',
				'commentaire' => $comment,
				'action_le' => now(),
			]);
		}

		// Envoyer au directeur de publication
		$director = User::whereHas('profile', function($q) {
			$q->where('role', 'directeur_publication');
		})->first();

		if ($director) {
			$this->update(['current_reviewer_id' => $director->id]);

			ArticleWorkflow::create([
				'article_id' => $this->id,
				'from_user_id' => auth()->id(),
				'to_user_id' => $director->id,
				'action' => 'submitted',
				'statut' => 'pending',
				'commentaire' => 'Article révisé et prêt pour approbation',
			]);

			$this->sendNotification($director->id, 'Article révisé et prêt pour approbation', $this->titre);
		}
	}

	public function approve($comment = null)
	{
		$this->update([
			'statut_workflow' => 'approved',
			'approuve_le' => now(),
		]);

		// Mettre à jour l'étape de workflow
		$workflow = $this->workflowSteps()->where('statut', 'pending')->first();
		if ($workflow) {
			$workflow->update([
				'statut' => 'completed',
				'action' => 'approved',
				'commentaire' => $comment,
				'action_le' => now(),
			]);
		}

		// Notifier l'auteur
		$this->sendNotification($this->created_by, 'Votre article a été approuvé', $this->titre);
	}

	public function reject($reason, $comment = null)
	{
		$this->update([
			'statut_workflow' => 'rejected',
			'raison_rejet' => $reason,
		]);

		// Mettre à jour l'étape de workflow
		$workflow = $this->workflowSteps()->where('statut', 'pending')->first();
		if ($workflow) {
			$workflow->update([
				'statut' => 'rejected',
				'action' => 'rejected',
				'commentaire' => $comment,
				'action_le' => now(),
			]);
		}

		// Notifier l'auteur
		$this->sendNotification($this->created_by, 'Votre article a été rejeté', $this->titre . ' - Raison: ' . $reason);
	}

	public function publish()
	{
		$this->update([
			'statut_workflow' => 'published',
			'statut' => 'published',
			'publie_le' => now(),
		]);

		// Mettre à jour l'étape de workflow
		$workflow = $this->workflowSteps()->where('statut', 'pending')->first();
		if ($workflow) {
			$workflow->update([
				'statut' => 'completed',
				'action' => 'published',
				'action_le' => now(),
			]);
		}

		// Notifier l'auteur
		$this->sendNotification($this->created_by, 'Votre article a été publié', $this->titre);
	}

	private function sendNotification($userId, $message, $title)
	{
		Notification::create([
			'user_id' => $userId,
			'type' => 'workflow',
			'titre' => $title,
			'message' => $message,
			'donnees' => [
				'article_id' => $this->id,
				'article_title' => $title,
			],
		]);
	}

	// Méthodes utilitaires pour les nouveaux champs
	public function calculateReadingTime()
	{
		$wordCount = $this->word_count ?? $this->calculateWordCount();
		$readingTime = ceil($wordCount / 200); // 200 mots par minute
		$this->update(['reading_time' => $readingTime]);
		return $readingTime;
	}

	public function calculateWordCount()
	{
		$content = strip_tags($this->contenu);
		$wordCount = str_word_count($content);
		$this->update(['word_count' => $wordCount]);
		return $wordCount;
	}

	public function calculateCharacterCount()
	{
		$characterCount = strlen(strip_tags($this->contenu));
		$this->update(['character_count' => $characterCount]);
		return $characterCount;
	}

	public function generateExcerpt($length = 150)
	{
		$content = strip_tags($this->contenu);
		$excerpt = substr($content, 0, $length);
		if (strlen($content) > $length) {
			$excerpt .= '...';
		}
		$this->update(['excerpt' => $excerpt]);
		return $excerpt;
	}

	// Scopes pour les nouveaux champs
	public function scopeByCategory($query, $category)
	{
		return $query->where('category', $category);
	}

	public function scopeFeatured($query)
	{
		return $query->where('is_featured', true);
	}

	public function scopeBreakingNews($query)
	{
		return $query->where('is_breaking_news', true);
	}

	public function scopeByLanguage($query, $language = 'fr')
	{
		return $query->where('language', $language);
	}

	public function scopeWithComments($query)
	{
		return $query->where('allow_comments', true);
	}
}


