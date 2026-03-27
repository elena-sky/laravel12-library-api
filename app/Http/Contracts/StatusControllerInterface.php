<?php

namespace App\Http\Contracts;

use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

/**
 * HTTP contract for status endpoints under /status/* (served at /api/v1/status/*).
 */
interface StatusControllerInterface
{
    /**
     * Liveness: the application process accepts HTTP traffic.
     *
     * This is not a full readiness check (dependencies such as the database are not validated here).
     * Use a separate readiness endpoint when you need to gate load balancers on backing services.
     */
    #[OA\Get(
        path: '/status/liveness',
        operationId: 'statusLiveness',
        summary: 'Liveness probe',
        description: 'Returns 200 when the app process is up. Does not verify database or other dependencies.',
        tags: ['Status'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Process is alive',
                content: new OA\JsonContent(
                    type: 'object',
                    required: ['data', 'message'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            required: ['status'],
                            properties: [
                                new OA\Property(property: 'status', type: 'string', example: 'ok'),
                            ]
                        ),
                        new OA\Property(property: 'message', type: 'string', example: 'Liveness check passed'),
                    ]
                )
            ),
        ]
    )]
    public function liveness(): JsonResponse;
}
