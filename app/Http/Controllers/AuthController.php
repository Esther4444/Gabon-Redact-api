<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Connexion sécurisée avec rate limiting et audit
     */
    public function login(Request $request)
    {
        // Rate limiting pour prévenir les attaques par force brute
        $key = 'login-attempts:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => 'Trop de tentatives de connexion. Réessayez dans ' . $seconds . ' secondes.',
                'retry_after' => $seconds
            ], 429);
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:journaliste,directeur_publication,secretaire_redaction,social_media_manager'],
            'device_info' => ['nullable', 'array'],
            'device_info.platform' => ['nullable', 'string'],
            'device_info.user_agent' => ['nullable', 'string'],
        ]);

        $user = User::with('profile')->where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            RateLimiter::hit($key, 300); // 5 minutes de blocage

            // Audit log pour tentative de connexion échouée
            AuditLog::create([
                'actor_id' => null,
                'action' => 'login_failed',
                'entity_type' => 'user',
                'entity_id' => $user?->id,
                'context' => [
                    'email' => $validated['email'],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'device_info' => $validated['device_info'] ?? null,
                ],
                'occurred_at' => now()
            ]);

            throw ValidationException::withMessages([
                'email' => ['Les identifiants sont invalides.'],
            ]);
        }

        // Vérifier que l'utilisateur est actif
        if (!$user->is_active) {
            RateLimiter::hit($key, 300);
            return response()->json([
                'success' => false,
                'message' => 'Votre compte est désactivé. Contactez l\'administrateur.',
            ], 403);
        }

        // Vérifier que l'utilisateur a le bon rôle
        $userRole = $user->profile->role ?? 'journaliste';
        if ($userRole !== $validated['role']) {
            RateLimiter::hit($key, 300);
            return response()->json([
                'success' => false,
                'message' => 'Le rôle sélectionné ne correspond pas à votre compte.',
                'errors' => [
                    'role' => ['Le rôle sélectionné ne correspond pas à votre compte.']
                ]
            ], 422);
        }

        // Créer le token avec expiration
        $token = $user->createToken('api', ['*'], now()->addHours(8))->plainTextToken;

        // Mettre à jour les informations de connexion
        $user->update([
            'last_login_at' => now(),
            'failed_login_attempts' => 0,
        ]);

        // Audit log pour connexion réussie
        AuditLog::create([
            'actor_id' => $user->id,
            'action' => 'login_success',
            'entity_type' => 'user',
            'entity_id' => $user->id,
            'context' => [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device_info' => $validated['device_info'] ?? null,
                'role' => $userRole,
            ],
            'occurred_at' => now()
        ]);

        // Effacer les tentatives de connexion échouées
        RateLimiter::clear($key);

        return response()->json([
            'success' => true,
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 28800, // 8 heures
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $userRole,
                    'permissions' => $user->getAllPermissions(),
                    'full_name' => $user->profile->full_name ?? $user->name,
                    'avatar_url' => $user->profile->avatar_url ?? null,
                    'last_login_at' => $user->last_login_at,
                ],
            ]
        ]);
    }

    /**
     * Déconnexion sécurisée avec audit
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        // Audit log pour déconnexion
        AuditLog::create([
            'actor_id' => $user->id,
            'action' => 'logout',
            'entity_type' => 'user',
            'entity_id' => $user->id,
            'context' => [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
            'occurred_at' => now()
        ]);

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie'
        ]);
    }

    /**
     * Renouvellement de token
     */
    public function refresh(Request $request)
    {
        $user = $request->user();

        // Révoquer l'ancien token
        $request->user()->currentAccessToken()->delete();

        // Créer un nouveau token
        $token = $user->createToken('api', ['*'], now()->addHours(8))->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 28800,
            ]
        ]);
    }

    /**
     * Obtenir les utilisateurs disponibles (avec permissions)
     */
    public function availableUsers(Request $request)
    {
        // Vérifier les permissions
        if (!$request->user()->can('viewAny', User::class)) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $users = User::with('profile')
            ->where('is_active', true)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->profile->role ?? 'journaliste',
                    'full_name' => $user->profile->full_name ?? $user->name,
                    'avatar_url' => $user->profile->avatar_url ?? null,
                    'last_login_at' => $user->last_login_at,
                ];
            });

        return response()->json(['success' => true, 'data' => $users]);
    }

    /**
     * Vérification 2FA (à implémenter)
     */
    public function verify2FA(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
            'method' => 'required|in:totp,sms,email',
        ]);

        // TODO: Implémenter la vérification 2FA
        return response()->json([
            'success' => false,
            'message' => '2FA non encore implémenté'
        ], 501);
    }
}


