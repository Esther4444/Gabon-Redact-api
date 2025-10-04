<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;

class TeamController extends Controller
{
	public function members(Request $request)
	{
		$query = Profile::query()->with('user');
		if ($role = $request->query('role')) { $query->where('role', $role); }
		if ($search = $request->query('q')) { $query->where('full_name','like',"%{$search}%"); }
		return response()->json(['success' => true, 'data' => $query->get()]);
	}

	public function updateRole($userId, Request $request)
	{
		$validated = $request->validate([
			'role' => ['required','string','in:admin,journaliste,chef_rubrique,directeur_redaction,social_media_manager'],
		]);
		$profile = Profile::where('user_id', $userId)->firstOrFail();
		$profile->role = $validated['role'];
		$profile->save();
		return response()->json(['success' => true, 'data' => $profile]);
	}

	public function removeMember($userId)
	{
		$user = User::findOrFail($userId);
		$user->delete();
		return response()->json(['success' => true], 204);
	}
}


