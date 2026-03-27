<?php

namespace App\Http\Contracts;

use App\Http\Requests\User\UpdateUserPasswordRequest;
use App\Http\Requests\User\UpdateUserRequest;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

/**
 * Authenticated current-user profile and credentials (Sanctum); self-service only — no /users/{id}.
 */
interface CurrentUserControllerInterface
{
    #[OA\Get(
        path: '/user',
        operationId: 'currentUserShow',
        summary: 'Current user profile',
        security: [['sanctum' => []]],
        tags: ['User'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Current authenticated user',
                content: new OA\JsonContent(ref: '#/components/schemas/UserDataResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function show(): JsonResponse;

    #[OA\Patch(
        path: '/user',
        operationId: 'currentUserUpdate',
        summary: 'Update current user profile',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateUserProfileRequestBody')
        ),
        tags: ['User'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated current user profile',
                content: new OA\JsonContent(ref: '#/components/schemas/UserDataResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
    public function update(UpdateUserRequest $request): JsonResponse;

    #[OA\Put(
        path: '/user/password',
        operationId: 'currentUserUpdatePassword',
        summary: 'Update current user password',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateUserPasswordRequestBody')
        ),
        tags: ['User'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Password updated successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
    public function updatePassword(UpdateUserPasswordRequest $request): JsonResponse;
}
