<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
	public function index(Request $request)
	{
		$query = User::with('profile');
		
		// Filtrer par rôle si spécifié
		if ($request->has('role')) {
			$role = $request->get('role');
			$query->whereHas('profile', function($q) use ($role) {
				$q->where('role', $role);
			});
		}
		
		$users = $query->get();
		
		return response()->json([
			'success' => true,
			'data' => $users->map(function($user) {
				return [
					'id' => $user->id,
					'name' => $user->name,
					'email' => $user->email,
					'role' => $user->profile?->role ?? 'journaliste',
					'full_name' => $user->profile?->nom_complet ?? $user->name,
					'avatar_url' => $user->profile?->url_avatar,
				];
			})
		]);
	}

	public function profile(Request $request)
	{
		$user = $request->user();

		// Créer un profil s'il n'existe pas
		if (!$user->profile) {
			$user->profile()->create([
				'nom_complet' => $user->name,
				'role' => 'journaliste',
			]);
		}

		$user->load('profile');
		return response()->json(['success' => true, 'data' => $user]);
	}

	public function updateProfile(Request $request)
	{
		$validated = $request->validate([
			'full_name' => ['sometimes','string','max:255'],
			'avatar_url' => ['sometimes','string','max:2048'],
			'preferences' => ['sometimes','array'],
		]);

		$user = $request->user();

		// Créer un profil s'il n'existe pas
		if (!$user->profile) {
			$user->profile()->create([
				'nom_complet' => $user->name,
				'role' => 'journaliste',
			]);
		}

		$profile = $user->profile;
		// Mapper les champs
		if (isset($validated['full_name'])) $profile->nom_complet = $validated['full_name'];
		if (isset($validated['avatar_url'])) $profile->url_avatar = $validated['avatar_url'];
		if (isset($validated['preferences'])) $profile->preferences = $validated['preferences'];
		$profile->save();
		return response()->json(['success' => true, 'data' => $profile]);
	}
}


