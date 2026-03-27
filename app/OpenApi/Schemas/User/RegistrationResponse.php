<?php

namespace App\OpenApi\Schemas\User;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RegistrationResponse',
    required: ['data', 'message'],
    properties: [
        new OA\Property(
            property: 'data',
            required: ['user', 'token', 'token_type'],
            properties: [
                new OA\Property(property: 'user', ref: '#/components/schemas/UserResource'),
                new OA\Property(property: 'token', type: 'string'),
                new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
            ],
            type: 'object'
        ),
        new OA\Property(property: 'message', type: 'string', example: 'Registration successful'),
    ]
)]
final class RegistrationResponse {}
