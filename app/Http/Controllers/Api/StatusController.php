<?php

namespace App\Http\Controllers\Api;

use App\Http\Contracts\StatusControllerInterface;
use App\Http\Controllers\Controller;
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
