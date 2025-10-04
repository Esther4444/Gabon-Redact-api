<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Réponse JSON standardisée pour les succès
     */
    protected function successResponse($data = null, string $message = 'Opération réussie', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Réponse JSON standardisée pour les erreurs
     */
    protected function errorResponse(string $message = 'Une erreur est survenue', $errors = null, int $statusCode = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Réponse JSON pour les ressources créées
     */
    protected function createdResponse($data = null, string $message = 'Ressource créée avec succès'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Réponse JSON pour les ressources supprimées
     */
    protected function deletedResponse(string $message = 'Ressource supprimée avec succès'): JsonResponse
    {
        return $this->successResponse(null, $message, 204);
    }

    /**
     * Réponse JSON pour les erreurs de validation
     */
    protected function validationErrorResponse($errors, string $message = 'Erreurs de validation'): JsonResponse
    {
        return $this->errorResponse($message, $errors, 422);
    }

    /**
     * Réponse JSON pour les erreurs d'autorisation
     */
    protected function unauthorizedResponse(string $message = 'Non autorisé'): JsonResponse
    {
        return $this->errorResponse($message, null, 403);
    }

    /**
     * Réponse JSON pour les ressources non trouvées
     */
    protected function notFoundResponse(string $message = 'Ressource non trouvée'): JsonResponse
    {
        return $this->errorResponse($message, null, 404);
    }
}
