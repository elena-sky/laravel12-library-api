<?php

namespace App\OpenApi\Schemas\User;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateUserProfileRequestBody',
    required: ['name', 'email'],
    properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'email', type: 'string', format: 'email'),
    ]
)]
final class UpdateUserProfileRequestBody {}
