<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function apiResponse(array $data = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'status_code' => $status,
            ...$data,
        ], $status);
    }
}
