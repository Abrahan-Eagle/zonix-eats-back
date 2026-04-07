<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

/**
 * Respuestas JSON canónicas Zonix: success, message, data (y opcional error_code / errors).
 */
trait ApiResponse
{
    protected function jsonSuccess(mixed $data = null, string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * @param  array<string, mixed>|null  $errors
     */
    protected function jsonError(
        string $message,
        int $status = 400,
        ?string $errorCode = null,
        mixed $errors = null,
        mixed $data = null
    ): JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
        ];
        if ($errorCode !== null) {
            $payload['error_code'] = $errorCode;
        }
        if ($errors !== null) {
            $payload['errors'] = $errors;
        }
        if ($data !== null) {
            $payload['data'] = $data;
        }

        return response()->json($payload, $status);
    }

    protected function jsonUnauthorized(string $message = 'No autenticado'): JsonResponse
    {
        return $this->jsonError($message, 401, 'UNAUTHENTICATED');
    }

    protected function jsonForbidden(string $message = 'No autorizado'): JsonResponse
    {
        return $this->jsonError($message, 403, 'FORBIDDEN');
    }

    protected function jsonNotFound(string $message = 'No encontrado'): JsonResponse
    {
        return $this->jsonError($message, 404, 'NOT_FOUND');
    }

    protected function jsonValidationError(mixed $errors, string $message = 'Error de validación'): JsonResponse
    {
        return $this->jsonError($message, 422, 'VALIDATION_ERROR', $errors);
    }
}
