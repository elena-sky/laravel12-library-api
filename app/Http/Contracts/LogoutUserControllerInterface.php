<?php

namespace App\Http\Contracts;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/**
 * Revoke the current Sanctum access token only.
 */
interface LogoutUserControllerInterface
{
    #[OA\Post(
        path: '/logout',
        operationId: 'logoutUser',
        summary: 'Log out (revoke current token)',
        security: [['sanctum' => []]],
        tags: ['User'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Current token revoked',
                content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function logout(Request $request): JsonResponse;
}
