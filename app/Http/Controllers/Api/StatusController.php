<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Interfaces\StatusControllerInterface;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * HTTP implementation of {@see StatusControllerInterface}.
 */
class StatusController extends Controller implements StatusControllerInterface
{
    /**
     * {@inheritDoc}
     */
    public function liveness(): JsonResponse
    {
        return ApiResponse::success(
            ['status' => 'ok'],
            'Liveness check passed'
        );
    }
}
