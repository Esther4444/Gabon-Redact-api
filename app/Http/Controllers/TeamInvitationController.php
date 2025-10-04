<?php

namespace App\Http\Controllers;

use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TeamInvitationController extends Controller
{
	public function create(Request $request)
	{
		$validated = $request->validate([
			'email' => ['required','email'],
			'role' => ['required','string','in:admin,journaliste,chef_rubrique,directeur_redaction,social_media_manager'],
		]);
		$inv = TeamInvitation::create([
			'email' => $validated['email'],
			'role' => $validated['role'],
			'token' => Str::uuid()->toString(),
			'invited_by' => $request->user()->id,
		]);
		return response()->json(['success' => true, 'data' => $inv], 201);
	}

	public function validateToken($token)
	{
		$inv = TeamInvitation::where('token', $token)->firstOrFail();
		return response()->json(['success' => true, 'data' => $inv]);
	}

	public function accept($token, Request $request)
	{
		$inv = TeamInvitation::where('token', $token)->firstOrFail();
		$user = User::firstOrCreate([
			'email' => $inv->email,
		], [
			'name' => $request->input('name', $inv->email),
			'password' => bcrypt(Str::random(16)),
		]);
		$user->profile()->updateOrCreate(['user_id' => $user->id], [
			'full_name' => $user->name,
			'role' => $inv->role,
		]);
		$inv->accepted_at = now();
		$inv->save();
		return response()->json(['success' => true, 'data' => $user]);
	}
}


