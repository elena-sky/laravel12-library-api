<?php

namespace App\Http\Contracts;

use App\Http\Requests\User\LoginUserRequest;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

/**
 * Public login (issues a new Sanctum personal access token).
 */
interface LoginUserControllerInterface
{
    #[OA\Post(
        path: '/login',
        operationId: 'loginUser',
        summary: 'Log in with email and password',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/LoginRequestBody')
        ),
        tags: ['User'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Authenticated; returns profile and API token',
                content: new OA\JsonContent(ref: '#/components/schemas/RegistrationResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid credentials',
                content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')
            ),
            new OA\Response(response: 422, description: 'Validation failed'),
            new OA\Response(response: 429, description: 'Too many requests'),
        ]
    )]
    public function login(LoginUserRequest $request): JsonResponse;
}
