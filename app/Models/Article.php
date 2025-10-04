<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
	use HasFactory, SoftDeletes;

	protected $fillable = [
		'title','slug','content','status','workflow_status','folder_id','created_by','assigned_to','current_reviewer_id',
		'seo_title','seo_description','seo_keywords','published_at','submitted_at','reviewed_at','approved_at',
		'rejection_reason','workflow_history','metadata',
	];

	protected $casts = [
		'published_at' => 'datetime',
		'submitted_at' => 'datetime',
		'reviewed_at' => 'datetime',
		'approved_at' => 'datetime',
		'seo_keywords' => 'array',
		'workflow_history' => 'array',
		'metadata' => 'array',
	];

	public function folder()
	{
		return $this->belongsTo(Folder::class);
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
		return $query->where('workflow_status', $status);
	}

	public function scopeForReviewer($query, $userId)
	{
		return $query->where('current_reviewer_id', $userId);
	}

	public function scopeSubmitted($query)
	{
		return $query->where('workflow_status', 'submitted');
	}

	public function scopeInReview($query)
	{
		return $query->where('workflow_status', 'in_review');
	}

	public function scopeApproved($query)
	{
		return $query->where('workflow_status', 'approved');
	}

	public function scopeRejected($query)
	{
		return $query->where('workflow_status', 'rejected');
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
			'workflow_status' => 'submitted',
			'current_reviewer_id' => $reviewerId,
			'submitted_at' => now(),
		]);

		// Créer une étape de workflow
		ArticleWorkflow::create([
			'article_id' => $this->id,
			'from_user_id' => $this->created_by,
			'to_user_id' => $reviewerId,
			'action' => 'submitted',
			'status' => 'pending',
			'comment' => $comment,
		]);

		// Envoyer une notification
		$this->sendNotification($reviewerId, 'Nouvel article soumis pour révision', $this->title);
	}

	public function review($comment = null)
	{
		$this->update([
			'workflow_status' => 'in_review',
			'reviewed_at' => now(),
		]);

		// Mettre à jour l'étape de workflow
		$workflow = $this->workflowSteps()->where('status', 'pending')->first();
		if ($workflow) {
			$workflow->update([
				'status' => 'completed',
				'action' => 'reviewed',
				'comment' => $comment,
				'action_at' => now(),
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
				'status' => 'pending',
				'comment' => 'Article révisé et prêt pour approbation',
			]);

			$this->sendNotification($director->id, 'Article révisé et prêt pour approbation', $this->title);
		}
	}

	public function approve($comment = null)
	{
		$this->update([
			'workflow_status' => 'approved',
			'approved_at' => now(),
		]);

		// Mettre à jour l'étape de workflow
		$workflow = $this->workflowSteps()->where('status', 'pending')->first();
		if ($workflow) {
			$workflow->update([
				'status' => 'completed',
				'action' => 'approved',
				'comment' => $comment,
				'action_at' => now(),
			]);
		}

		// Notifier l'auteur
		$this->sendNotification($this->created_by, 'Votre article a été approuvé', $this->title);
	}

	public function reject($reason, $comment = null)
	{
		$this->update([
			'workflow_status' => 'rejected',
			'rejection_reason' => $reason,
		]);

		// Mettre à jour l'étape de workflow
		$workflow = $this->workflowSteps()->where('status', 'pending')->first();
		if ($workflow) {
			$workflow->update([
				'status' => 'rejected',
				'action' => 'rejected',
				'comment' => $comment,
				'action_at' => now(),
			]);
		}

		// Notifier l'auteur
		$this->sendNotification($this->created_by, 'Votre article a été rejeté', $this->title . ' - Raison: ' . $reason);
	}

	public function publish()
	{
		$this->update([
			'workflow_status' => 'published',
			'status' => 'published',
			'published_at' => now(),
		]);

		// Mettre à jour l'étape de workflow
		$workflow = $this->workflowSteps()->where('status', 'pending')->first();
		if ($workflow) {
			$workflow->update([
				'status' => 'completed',
				'action' => 'published',
				'action_at' => now(),
			]);
		}

		// Notifier l'auteur
		$this->sendNotification($this->created_by, 'Votre article a été publié', $this->title);
	}

	private function sendNotification($userId, $message, $title)
	{
		Notification::create([
			'user_id' => $userId,
			'type' => 'workflow',
			'message' => $message,
			'data' => [
				'article_id' => $this->id,
				'article_title' => $title,
			],
		]);
	}
}


