<?php

namespace App\Http\Response;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function errorResponse(string $message, $error = null, int $errorCode = 400): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'errors' => $error ?? null,
        ], $errorCode);
    }

    public static function successResponse(string $message, $data = null): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data ?? null,
        ], 200);
    }

    public static function handleResponse(callable $callback)
    {
        try {
            return $callback();
        } catch (\Throwable $th) {
            return self::errorResponse('Server Error', $th->getMessage(), 500);
        }
    }
}
