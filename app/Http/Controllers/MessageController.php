<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Obtenir les messages de l'utilisateur connecté
     */
    public function index(Request $request)
    {
        $query = Message::with(['sender.profile', 'article'])
            ->where('recipient_id', Auth::id())
            ->orderBy('created_at', 'desc');

        // Filtrer par statut de lecture
        if ($request->has('unread_only') && $request->boolean('unread_only')) {
            $query->where('is_read', false);
        }

        // Filtrer par article
        if ($request->has('article_id')) {
            $query->where('article_id', $request->article_id);
        }

        $messages = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $messages->items(),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ]
        ]);
    }

    /**
     * Envoyer un nouveau message
     */
    public function store(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:5000',
            'article_id' => 'nullable|exists:articles,id',
            'parent_message_id' => 'nullable|exists:messages,id',
        ]);

        $message = Message::create([
            'sender_id' => Auth::id(),
            'recipient_id' => $request->recipient_id,
            'subject' => $request->subject,
            'body' => $request->body,
            'article_id' => $request->article_id,
            'parent_message_id' => $request->parent_message_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message envoyé',
            'data' => $message->load(['sender.profile', 'recipient.profile', 'article'])
        ], 201);
    }

    /**
     * Afficher un message spécifique
     */
    public function show(Message $message)
    {
        // Vérifier que l'utilisateur peut voir ce message
        if ($message->sender_id !== Auth::id() && $message->recipient_id !== Auth::id()) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        // Marquer comme lu si c'est le destinataire
        if ($message->recipient_id === Auth::id() && !$message->is_read) {
            $message->markAsRead();
        }

        $message->load(['sender.profile', 'recipient.profile', 'article', 'replies.sender.profile']);

        return response()->json([
            'success' => true,
            'data' => $message
        ]);
    }

    /**
     * Répondre à un message
     */
    public function reply(Request $request, Message $message)
    {
        $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        // Vérifier que l'utilisateur peut répondre à ce message
        if ($message->recipient_id !== Auth::id() && $message->sender_id !== Auth::id()) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $reply = Message::create([
            'sender_id' => Auth::id(),
            'recipient_id' => $message->sender_id === Auth::id() ? $message->recipient_id : $message->sender_id,
            'subject' => 'Re: ' . $message->subject,
            'body' => $request->body,
            'article_id' => $message->article_id,
            'parent_message_id' => $message->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Réponse envoyée',
            'data' => $reply->load(['sender.profile', 'recipient.profile', 'article'])
        ], 201);
    }

    /**
     * Marquer un message comme lu
     */
    public function markAsRead(Message $message)
    {
        if ($message->recipient_id !== Auth::id()) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $message->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Message marqué comme lu'
        ]);
    }

    /**
     * Marquer un message comme non lu
     */
    public function markAsUnread(Message $message)
    {
        if ($message->recipient_id !== Auth::id()) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $message->markAsUnread();

        return response()->json([
            'success' => true,
            'message' => 'Message marqué comme non lu'
        ]);
    }

    /**
     * Supprimer un message
     */
    public function destroy(Message $message)
    {
        // Vérifier que l'utilisateur peut supprimer ce message
        if ($message->sender_id !== Auth::id() && $message->recipient_id !== Auth::id()) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $message->delete();

        return response()->json([
            'success' => true,
            'message' => 'Message supprimé'
        ]);
    }

    /**
     * Obtenir les messages non lus
     */
    public function unread()
    {
        $unreadCount = Message::where('recipient_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'data' => ['unread_count' => $unreadCount]
        ]);
    }

    /**
     * Obtenir les conversations (messages groupés par correspondant)
     */
    public function conversations()
    {
        $conversations = Message::with(['sender.profile', 'recipient.profile', 'article'])
            ->where(function($query) {
                $query->where('sender_id', Auth::id())
                      ->orWhere('recipient_id', Auth::id());
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function($message) {
                // Grouper par correspondant
                return $message->sender_id === Auth::id()
                    ? $message->recipient_id
                    : $message->sender_id;
            })
            ->map(function($messages) {
                $latestMessage = $messages->first();
                $correspondent = $latestMessage->sender_id === Auth::id()
                    ? $latestMessage->recipient
                    : $latestMessage->sender;

                return [
                    'correspondent' => $correspondent,
                    'latest_message' => $latestMessage,
                    'unread_count' => $messages->where('recipient_id', Auth::id())->where('is_read', false)->count(),
                    'total_messages' => $messages->count(),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $conversations
        ]);
    }
}
