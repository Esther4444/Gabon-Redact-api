<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'error' => 'Non authentifié',
                'message' => 'Token d\'authentification requis'
            ], 401);
        }

        if (!$user->hasPermission($permission)) {
            return response()->json([
                'error' => 'Permissions insuffisantes',
                'message' => 'Vous n\'avez pas les permissions nécessaires pour effectuer cette action',
                'required_permission' => $permission,
                'user_permissions' => $user->getAllPermissions()
            ], 403);
        }

        return $next($request);
    }
}
