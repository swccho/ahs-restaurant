<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(mixed $data = null, ?string $message = null, int $code = 200): JsonResponse
    {
        $body = [
            'success' => true,
            'message' => $message,
        ];
        if ($data !== null) {
            $body['data'] = $data;
        }
        return response()->json($body, $code);
    }

    public static function error(string $message, int $code = 400, ?array $errors = null): JsonResponse
    {
        $body = [
            'success' => false,
            'message' => $message,
        ];
        if ($errors !== null) {
            $body['errors'] = $errors;
        }
        return response()->json($body, $code);
    }
}
