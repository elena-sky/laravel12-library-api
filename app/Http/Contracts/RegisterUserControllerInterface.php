<?php

namespace App\Http\Contracts;

use App\Http\Requests\User\StoreUserRequest;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

/**
 * Public registration (issues a Sanctum personal access token).
 */
interface RegisterUserControllerInterface
{
    #[OA\Post(
        path: '/register',
        operationId: 'registerUser',
        summary: 'Register a new user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/RegisterRequestBody')
        ),
        tags: ['User'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'User created; returns profile and API token',
                content: new OA\JsonContent(ref: '#/components/schemas/RegistrationResponse')
            ),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
    public function store(StoreUserRequest $request): JsonResponse;
}
