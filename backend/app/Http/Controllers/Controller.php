<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /**
     * Return success response.
     */
    protected function successResponse(mixed $data = null, ?string $message = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Return error response.
     */
    protected function errorResponse(string $message, int $code = 400, array $errors = []): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Return paginated response.
     */
    protected function paginatedResponse(LengthAwarePaginator $paginator, ?string $message = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ], $code);
    }
}
