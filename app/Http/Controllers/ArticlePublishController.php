<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Support\Carbon;

class ArticlePublishController extends Controller
{
	public function publish($id)
	{
		$article = Article::findOrFail($id);
		$article->status = 'published';
		$article->published_at = Carbon::now();
		$article->save();
		return response()->json(['success' => true, 'data' => $article]);
	}
}


