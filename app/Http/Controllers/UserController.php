<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
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


