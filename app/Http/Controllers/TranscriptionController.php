<?php

namespace App\Http\Controllers;

use App\Models\Media;

class TranscriptionController extends Controller
{
	public function transcribe($mediaId)
	{
		$media = Media::findOrFail($mediaId);
		return response()->json(['success' => true, 'data' => ['status' => 'queued', 'media_id' => $media->id]]);
	}
}


