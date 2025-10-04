<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
	public function upload(Request $request)
	{
		$validated = $request->validate([
			'file' => ['required','file','max:20480'],
		]);
		$file = $validated['file'];
		$path = $file->store('uploads', 'public');
		$media = Media::create([
			'user_id' => $request->user()->id,
			'path' => $path,
			'mime_type' => $file->getMimeType(),
			'size_bytes' => $file->getSize(),
		]);
		return response()->json(['success' => true, 'data' => $media], 201);
	}

	public function index(Request $request)
	{
		$query = Media::query();
		if (!$request->user()->can('viewAnyMedia')) {
			$query->where('user_id', $request->user()->id);
		}
		return response()->json(['success' => true, 'data' => $query->orderByDesc('created_at')->get()]);
	}

	public function destroy($id)
	{
		$media = Media::findOrFail($id);
		Storage::disk($media->disk)->delete($media->path);
		$media->delete();
		return response()->json(['success' => true], 204);
	}
}


