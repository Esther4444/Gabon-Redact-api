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
				'full_name' => $user->name,
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
				'full_name' => $user->name,
				'role' => 'journaliste',
			]);
		}

		$profile = $user->profile;
		$profile->fill($validated);
		$profile->save();
		return response()->json(['success' => true, 'data' => $profile]);
	}
}


