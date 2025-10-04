<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

	public function login(Request $request)
	{
		$validated = $request->validate([
			'email' => ['required','email'],
			'password' => ['required','string'],
			'role' => ['required','string','in:journaliste,directeur_publication,secretaire_redaction'],
		]);

		$user = User::with('profile')->where('email', $validated['email'])->first();
		if (!$user || !Hash::check($validated['password'], $user->password)) {
			throw ValidationException::withMessages([
				'email' => ['Les identifiants sont invalides.'],
			]);
		}

		// Vérifier que l'utilisateur a le bon rôle
		$userRole = $user->profile->role ?? 'journaliste';
		if ($userRole !== $validated['role']) {
			return response()->json([
				'success' => false,
				'message' => 'Le rôle sélectionné ne correspond pas à votre compte.',
				'errors' => [
					'role' => ['Le rôle sélectionné ne correspond pas à votre compte.']
				]
			], 422);
		}

		$token = $user->createToken('api')->plainTextToken;

		return response()->json(['success' => true, 'data' => [
			'token' => $token,
			'user' => [
				'id' => $user->id,
				'name' => $user->name,
				'email' => $user->email,
				'role' => $userRole,
				'full_name' => $user->profile->full_name ?? $user->name,
				'avatar_url' => $user->profile->avatar_url ?? null,
			],
		]]);
	}

	public function logout(Request $request)
	{
		$request->user()->currentAccessToken()->delete();
		return response()->json(['success' => true]);
	}

	public function availableUsers()
	{
		$users = User::with('profile')->get()->map(function ($user) {
			return [
				'id' => $user->id,
				'name' => $user->name,
				'email' => $user->email,
				'role' => $user->profile->role ?? 'journaliste',
				'full_name' => $user->profile->full_name ?? $user->name,
			];
		});

		return response()->json(['success' => true, 'data' => $users]);
	}
}


