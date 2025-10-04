<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleSlugController extends Controller
{
	public function generate($id, Request $request)
	{
		$validated = $request->validate([
			'title' => ['required','string','max:255']
		]);
		$slug = Str::slug($validated['title']);
		$base = $slug; $i = 2;
		while (Article::where('slug', $slug)->exists()) { $slug = $base.'-'.$i++; }
		return response()->json(['success' => true, 'data' => ['slug' => $slug]]);
	}
}


