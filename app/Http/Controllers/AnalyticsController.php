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
			'type_evenement' => $validated['event_type'],
			'proprietes' => $validated['properties'] ?? [],
			'survenu_le' => $validated['occurred_at'] ?? now(),
		]);
		return response()->json(['success' => true, 'data' => $event], 201);
	}

	public function dashboard(Request $request)
	{
		$countsByStatus = Article::select('statut', DB::raw('count(*) as total'))
			->groupBy('statut')->pluck('total','statut');
		return response()->json(['success' => true, 'data' => [
			'articles_by_status' => $countsByStatus,
		]]);
	}
}


