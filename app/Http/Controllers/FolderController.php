<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use Illuminate\Http\Request;

class FolderController extends Controller
{
	public function index(Request $request)
	{
		// Temporairement, retourner tous les dossiers pour tester
		$folders = Folder::all();
		return response()->json(['success' => true, 'data' => $folders]);
	}

	public function store(Request $request)
	{
		$validated = $request->validate([
			'name' => ['required','string','max:255'],
		]);
		$folder = Folder::create([
			'owner_id' => $request->user()->id,
			'nom' => $validated['name'],
		]);
		return response()->json(['success' => true, 'data' => $folder], 201);
	}

	public function show(Folder $folder)
	{
		return response()->json(['success' => true, 'data' => $folder]);
	}

	public function update(Request $request, Folder $folder)
	{
		$validated = $request->validate([
			'name' => ['required','string','max:255'],
		]);
		$folder->nom = $validated['name'];
		$folder->save();
		return response()->json(['success' => true, 'data' => $folder]);
	}

	public function destroy(Folder $folder)
	{
		$folder->delete();
		return response()->json(['success' => true], 204);
	}
}


