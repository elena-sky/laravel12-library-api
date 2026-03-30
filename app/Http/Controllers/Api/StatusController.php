<?php

namespace App\Http\Controllers\Api;

use App\Http\Contracts\StatusControllerInterface;
use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

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

    /**
     * {@inheritDoc}
     */
    public function readiness(): JsonResponse
    {
        try {
            DB::connection()->getPdo();
            DB::select('select 1 as readiness_check');
        } catch (Throwable) {
            return ApiResponse::error('Readiness check failed: database unavailable', 503);
        }

        return ApiResponse::success(
            [
                'status' => 'ok',
                'database' => 'ok',
            ],
            'Readiness check passed'
        );
    }
}
