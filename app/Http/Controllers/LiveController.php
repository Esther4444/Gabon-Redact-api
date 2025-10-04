<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LiveController extends Controller
{
	public function start(Request $request)
	{
		return response()->json(['success' => true, 'data' => ['live_id' => uniqid('live_')]]);
	}

	public function end($liveId)
	{
		return response()->json(['success' => true, 'data' => ['ended' => $liveId]]);
	}

	public function recording($liveId)
	{
		return response()->json(['success' => true, 'data' => ['url' => '/storage/recordings/'.$liveId.'.mp4']]);
	}
}


