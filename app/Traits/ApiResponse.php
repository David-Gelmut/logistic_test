<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    public function createResponse(string $status, string $message, int $code): JsonResponse
    {
        return response()->json([
            'status' => $status,
            'message' => $message
        ], $code);
    }
}
