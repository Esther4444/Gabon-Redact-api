<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        // Ne logger que les actions importantes
        if ($this->shouldAudit($request, $response)) {
            $this->logAudit($request, $response, $startTime);
        }

        return $response;
    }

    /**
     * Déterminer si la requête doit être auditée
     */
    private function shouldAudit(Request $request, Response $response): bool
    {
        // Ne pas logger les requêtes GET simples (sauf pour les ressources sensibles)
        if ($request->isMethod('GET') && !$this->isSensitiveGet($request)) {
            return false;
        }

        // Ne pas logger les requêtes qui échouent avec des erreurs 4xx (sauf 401, 403)
        if ($response->getStatusCode() >= 400 && $response->getStatusCode() < 500) {
            return in_array($response->getStatusCode(), [401, 403]);
        }

        // Logger toutes les autres requêtes (POST, PUT, DELETE, PATCH)
        return true;
    }

    /**
     * Vérifier si une requête GET est sensible
     */
    private function isSensitiveGet(Request $request): bool
    {
        $sensitivePaths = [
            'audit-logs',
            'users',
            'team/members',
            'analytics',
        ];

        foreach ($sensitivePaths as $path) {
            if (str_contains($request->path(), $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Enregistrer l'audit log
     */
    private function logAudit(Request $request, Response $response, float $startTime): void
    {
        $user = $request->user();
        $responseTime = round((microtime(true) - $startTime) * 1000, 2); // en millisecondes

        // Extraire l'ID de l'entité depuis l'URL si possible
        $entityId = $this->extractEntityId($request);
        $entityType = $this->extractEntityType($request);

        AuditLog::create([
            'actor_id' => $user?->id,
            'action' => $this->getActionName($request),
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'context' => [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'path' => $request->path(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'request_data' => $this->sanitizeRequestData($request),
                'response_status' => $response->getStatusCode(),
                'response_time_ms' => $responseTime,
                'content_length' => strlen($response->getContent()),
            ],
            'occurred_at' => now()
        ]);
    }

    /**
     * Extraire l'ID de l'entité depuis l'URL
     */
    private function extractEntityId(Request $request): ?int
    {
        $segments = $request->segments();

        // Chercher un ID numérique dans les segments
        foreach ($segments as $segment) {
            if (is_numeric($segment)) {
                return (int) $segment;
            }
        }

        return null;
    }

    /**
     * Extraire le type d'entité depuis l'URL
     */
    private function extractEntityType(Request $request): ?string
    {
        $segments = $request->segments();

        // Ignorer les préfixes d'API
        $segments = array_filter($segments, function($segment) {
            return !in_array($segment, ['api', 'v1']);
        });

        // Le premier segment restant est généralement le type d'entité
        return $segments[0] ?? null;
    }

    /**
     * Obtenir le nom de l'action
     */
    private function getActionName(Request $request): string
    {
        $method = $request->method();
        $path = $request->path();

        // Actions spéciales
        if (str_contains($path, 'login')) return 'login';
        if (str_contains($path, 'logout')) return 'logout';
        if (str_contains($path, 'publish')) return 'publish';
        if (str_contains($path, 'approve')) return 'approve';
        if (str_contains($path, 'reject')) return 'reject';

        // Actions CRUD standard
        return match($method) {
            'GET' => 'read',
            'POST' => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => strtolower($method)
        };
    }

    /**
     * Nettoyer les données de requête pour l'audit
     */
    private function sanitizeRequestData(Request $request): array
    {
        $data = $request->except(['password', 'password_confirmation', 'token', 'api_token']);

        // Limiter la taille des données
        if (strlen(json_encode($data)) > 1000) {
            return ['data_too_large' => true, 'size' => strlen(json_encode($data))];
        }

        return $data;
    }
}
