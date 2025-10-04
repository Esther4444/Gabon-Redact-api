<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEvent;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
	public function store(Request $request)
	{
		$validated = $request->validate([
			'event_type' => ['required','string','max:255'],
			'properties' => ['sometimes','array'],
			'occurred_at' => ['sometimes','date'],
		]);
		$event = AnalyticsEvent::create([
			'user_id' => optional($request->user())->id,
			'event_type' => $validated['event_type'],
			'properties' => $validated['properties'] ?? [],
			'occurred_at' => $validated['occurred_at'] ?? now(),
		]);
		return response()->json(['success' => true, 'data' => $event], 201);
	}

	public function dashboard(Request $request)
	{
		$countsByStatus = Article::select('status', DB::raw('count(*) as total'))
			->groupBy('status')->pluck('total','status');
		return response()->json(['success' => true, 'data' => [
			'articles_by_status' => $countsByStatus,
		]]);
	}
}


