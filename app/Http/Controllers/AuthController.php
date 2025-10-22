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
     * Connexion simplifiée pour test
     */
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ]);

            $user = User::with('profile')->where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Identifiants invalides',
                ], 401);
            }

            if (!$user->est_actif) {
                return response()->json([
                    'success' => false,
                    'message' => 'Compte désactivé',
                ], 403);
            }

            $userRole = $user->profile->role ?? 'journaliste';
            $token = $user->createToken('api')->plainTextToken;

            return response()->json([
                'success' => true,
                'data' => [
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => 28800,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $userRole,
                        'detection_method' => 'profile_database',
                        'full_name' => $user->profile->nom_complet ?? $user->name,
                        'avatar_url' => $user->profile->url_avatar ?? null,
                        'last_login_at' => $user->derniere_connexion_le,
                    ],
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur login: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage(),
            ], 500);
        }
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
            'type_entite' => 'user',
            'entite_id' => $user->id,
            'contexte' => [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
            'survenu_le' => now()
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
            ->where('est_actif', true)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->profile->role ?? 'journaliste',
                    'full_name' => $user->profile->nom_complet ?? $user->name,
                    'avatar_url' => $user->profile->url_avatar ?? null,
                    'last_login_at' => $user->derniere_connexion_le,
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

    /**
     * Détection simple du rôle utilisateur (Option 1)
     * Utilise uniquement le rôle stocké dans la table profils
     */
    private function detectUserRole(User $user): string
    {
        // 1. Recharger l'utilisateur avec le profil pour éviter les problèmes de cache
        $user->load('profile');

        // 2. Récupérer le rôle depuis le profil utilisateur
        $profileRole = $user->profile?->role ?? null;

        // 3. Si aucun profil, créer un profil par défaut
        if (!$user->profile) {
            $this->createDefaultProfile($user);
            $profileRole = 'journaliste';
        }

        // 4. Validation et sécurisation du rôle
        $validRoles = [
            'journaliste',
            'directeur_publication',
            'secretaire_redaction',
            'social_media_manager',
            'administrateur'
        ];

        // 5. Si le rôle n'est pas valide, utiliser journaliste par défaut
        if (!in_array($profileRole, $validRoles)) {
            $profileRole = 'journaliste';

            // Mettre à jour le profil avec le rôle par défaut
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                ['role' => 'journaliste']
            );
        }

        // 6. Log de la détection pour audit (avec try-catch pour éviter les erreurs)
        try {
            AuditLog::create([
                'actor_id' => $user->id,
                'action' => 'role_detected',
                'type_entite' => 'user',
                'entite_id' => $user->id,
                'contexte' => [
                    'detected_role' => $profileRole,
                    'detection_method' => 'profile_database',
                    'user_email' => $user->email,
                    'profile_exists' => $user->profile !== null,
                    'ip_address' => request()->ip(),
                ],
                'survenu_le' => now()
            ]);
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas faire échouer la connexion
            \Log::error('Erreur lors de la création du log d\'audit: ' . $e->getMessage());
        }

        return $profileRole;
    }

    /**
     * Créer un profil par défaut pour un utilisateur
     */
    private function createDefaultProfile(User $user): void
    {
        try {
            Profile::create([
                'user_id' => $user->id,
                'nom_complet' => $user->name,
                'role' => 'journaliste',
                'preferences' => null,
            ]);

            // Log de création de profil (avec try-catch)
            try {
                AuditLog::create([
                    'actor_id' => $user->id,
                    'action' => 'default_profile_created',
                    'type_entite' => 'profile',
                    'entite_id' => $user->id,
                    'contexte' => [
                        'user_email' => $user->email,
                        'default_role' => 'journaliste',
                        'reason' => 'no_profile_found'
                    ],
                    'survenu_le' => now()
                ]);
            } catch (\Exception $e) {
                \Log::error('Erreur lors de la création du log d\'audit pour profil: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création du profil par défaut: ' . $e->getMessage());
            throw $e; // Re-lancer l'erreur car c'est critique
        }
    }
}


