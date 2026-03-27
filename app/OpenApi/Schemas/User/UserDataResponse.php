<?php

namespace App\OpenApi\Schemas\User;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UserDataResponse',
    required: ['data'],
    properties: [
        new OA\Property(property: 'data', ref: '#/components/schemas/UserResource'),
        new OA\Property(property: 'message', type: 'string', example: 'Current user profile updated'),
    ]
)]
final class UserDataResponse {}
