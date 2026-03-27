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
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    required: ['name', 'email', 'password', 'password_confirmation'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'Ada Lovelace'),
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'ada@example.com'),
                        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'correct-horse-battery-staple'),
                        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
                    ]
                )
            )
        ),
        tags: ['User'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'User created; returns profile and API token',
                content: new OA\JsonContent(
                    required: ['data', 'message'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            required: ['user', 'token', 'token_type'],
                            properties: [
                                new OA\Property(
                                    property: 'user',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                        new OA\Property(property: 'name', type: 'string'),
                                        new OA\Property(property: 'email', type: 'string', format: 'email'),
                                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                                    ]
                                ),
                                new OA\Property(property: 'token', type: 'string'),
                                new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                            ]
                        ),
                        new OA\Property(property: 'message', type: 'string', example: 'Registration successful'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
    public function store(StoreUserRequest $request): JsonResponse;
}
