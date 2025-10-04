<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PodcastController extends Controller
{
	public function upload(Request $request)
	{
		$request->validate(['file' => ['required','file','max:51200']]);
		return response()->json(['success' => true, 'data' => ['podcast_id' => uniqid('pod_')]], 201);
	}

	public function snippets($podcastId)
	{
		return response()->json(['success' => true, 'data' => ['snippets' => []]]);
	}
}


