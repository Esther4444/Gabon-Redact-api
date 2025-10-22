<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * Force les requêtes API à accepter JSON et empêche les redirections
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Force l'en-tête Accept à application/json pour toutes les requêtes API
        $request->headers->set('Accept', 'application/json');

        $response = $next($request);

        // Si la réponse est une redirection et que c'est une requête API,
        // retourne une erreur JSON au lieu de rediriger
        if ($response->isRedirection() && $this->isApiRequest($request)) {
            $statusCode = $response->getStatusCode();

            // Redirection d'authentification (302)
            if ($statusCode === 302) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthenticated',
                    'message' => 'Token d\'authentification requis ou invalide. Veuillez vous reconnecter.',
                    'code' => 'AUTH_REQUIRED'
                ], 401);
            }

            // Autres redirections
            return response()->json([
                'success' => false,
                'error' => 'Unexpected redirect',
                'message' => 'La requête a tenté une redirection inattendue.',
                'redirect_to' => $response->headers->get('Location'),
                'code' => 'UNEXPECTED_REDIRECT'
            ], 400);
        }

        return $response;
    }

    /**
     * Vérifie si c'est une requête API
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    private function isApiRequest(Request $request): bool
    {
        // Vérifie si l'URL commence par /api
        return $request->is('api/*') ||
               $request->expectsJson() ||
               $request->wantsJson() ||
               $request->header('Accept') === 'application/json';
    }
}





