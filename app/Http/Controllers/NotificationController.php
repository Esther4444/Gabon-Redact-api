<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
	public function index(Request $request)
	{
		$items = Notification::where('user_id', $request->user()->id)->orderByDesc('created_at')->get();
		return response()->json(['success' => true, 'data' => $items]);
	}

	public function store(Request $request)
	{
		$validated = $request->validate([
			'user_id' => ['nullable', 'exists:users,id'],
			'title' => ['required', 'string', 'max:255'],
			'message' => ['required', 'string'],
			'type' => ['nullable', 'string'],
			'data' => ['nullable', 'array'],
		]);

		// Si user_id n'est pas fourni, utiliser l'utilisateur authentifié
		if (!$validated['user_id']) {
			$validated['user_id'] = $request->user()->id;
		}

		$notification = Notification::create($validated);
		return response()->json(['success' => true, 'data' => $notification], 201);
	}

	public function sendWorkflowNotification(Request $request)
	{
		$validated = $request->validate([
			'type' => ['required', 'string', 'in:article_review_request,article_reviewed,article_approved,article_rejected,article_published'],
			'title' => ['required', 'string', 'max:255'],
			'message' => ['required', 'string'],
			'data' => ['nullable', 'array'],
			'recipient_role' => ['nullable', 'string', 'in:secretaire_redaction,directeur_publication,journaliste'],
		]);

		$notification = null;

		// Si un rôle de destinataire est spécifié, envoyer à tous les utilisateurs de ce rôle
		if ($validated['recipient_role']) {
			$users = \App\Models\User::whereHas('profile', function($query) use ($validated) {
				$query->where('role', $validated['recipient_role']);
			})->get();

			foreach ($users as $user) {
				$notification = Notification::create([
					'user_id' => $user->id,
					'title' => $validated['title'],
					'message' => $validated['message'],
					'type' => $validated['type'],
					'data' => $validated['data'],
				]);
			}
		} else {
			// Sinon, envoyer à l'utilisateur authentifié
			$notification = Notification::create([
				'user_id' => $request->user()->id,
				'title' => $validated['title'],
				'message' => $validated['message'],
				'type' => $validated['type'],
				'data' => $validated['data'],
			]);
		}

		return response()->json(['success' => true, 'data' => $notification], 201);
	}

	public function markRead($id, Request $request)
	{
		$notification = Notification::where('user_id', $request->user()->id)->findOrFail($id);
		$notification->read = true;
		$notification->save();
		return response()->json(['success' => true]);
	}
}


