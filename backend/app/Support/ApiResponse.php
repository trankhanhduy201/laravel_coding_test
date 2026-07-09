<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiResponse
{
    public static function success(string $message = '', mixed $data = [], int $status = 200, ?array $meta = null): JsonResponse
    {
        if ($data instanceof JsonResource) {
            $data = $data->resolve();
        }

        if (is_array($data) && array_key_exists('data', $data)) {
            if ($meta === null && isset($data['meta']) && is_array($data['meta'])) {
                $meta = $data['meta'];
            }

            $data = $data['data'];
        }

        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if ($meta !== null) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $status);
    }

    public static function error(string $message = '', array|object $errors = [], int $status = 422): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
