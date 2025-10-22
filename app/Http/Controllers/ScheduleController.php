<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\PublicationSchedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
	public function index()
	{
		return response()->json(['success' => true, 'data' => PublicationSchedule::with('article')->orderByDesc('planifie_pour')->get()]);
	}

	public function store($id, Request $request)
	{
		$article = Article::findOrFail($id);
		$validated = $request->validate([
			'scheduled_for' => ['required','date','after:now'],
			'channel' => ['nullable','string','max:255'],
		]);
		$schedule = PublicationSchedule::create([
			'article_id' => $article->id,
			'planifie_pour' => $validated['scheduled_for'],
			'canal' => $validated['channel'] ?? null,
			'statut' => 'pending',
		]);
		return response()->json(['success' => true, 'data' => $schedule], 201);
	}

	public function update($scheduleId, Request $request)
	{
		$schedule = PublicationSchedule::findOrFail($scheduleId);
		$validated = $request->validate([
			'scheduled_for' => ['sometimes','date','after:now'],
			'channel' => ['sometimes','nullable','string','max:255'],
			'status' => ['sometimes','string','in:pending,done,cancelled,failed'],
		]);
		// Mapper les champs
		if (isset($validated['scheduled_for'])) $schedule->planifie_pour = $validated['scheduled_for'];
		if (isset($validated['channel'])) $schedule->canal = $validated['channel'];
		if (isset($validated['status'])) $schedule->statut = $validated['status'];
		$schedule->save();
		return response()->json(['success' => true, 'data' => $schedule]);
	}

	public function destroy($scheduleId)
	{
		$schedule = PublicationSchedule::findOrFail($scheduleId);
		$schedule->delete();
		return response()->json(['success' => true], 204);
	}
}


