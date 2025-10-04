<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
	public function index($id)
	{
		$comments = Comment::where('article_id', $id)->with('author.profile')->orderBy('created_at')->get();
		return response()->json(['success' => true, 'data' => $comments]);
	}

	public function store($id, Request $request)
	{
		$article = Article::findOrFail($id);
		$validated = $request->validate([
			'body' => ['required','string'],
		]);
		$comment = Comment::create([
			'article_id' => $article->id,
			'author_id' => $request->user()->id,
			'body' => $validated['body'],
		]);
		return response()->json(['success' => true, 'data' => $comment], 201);
	}

	public function update($commentId, Request $request)
	{
		$comment = Comment::findOrFail($commentId);
		$validated = $request->validate([
			'body' => ['required','string'],
		]);
		$comment->update($validated);
		return response()->json(['success' => true, 'data' => $comment]);
	}

	public function destroy($commentId)
	{
		$comment = Comment::findOrFail($commentId);
		$comment->delete();
		return response()->json(['success' => true], 204);
	}
}


