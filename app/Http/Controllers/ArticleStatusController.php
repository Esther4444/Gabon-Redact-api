<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleStatusController extends Controller
{
	public function update($id, Request $request)
	{
		$article = Article::findOrFail($id);
		$validated = $request->validate([
			'status' => ['required','string','in:draft,in_review,approved,scheduled,published,rejected,archived'],
		]);
		$article->status = $validated['status'];
		$article->save();
		return response()->json(['success' => true, 'data' => $article]);
	}
}


