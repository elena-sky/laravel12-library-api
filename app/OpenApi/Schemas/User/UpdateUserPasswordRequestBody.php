<?php

namespace App\OpenApi\Schemas\User;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateUserPasswordRequestBody',
    required: ['current_password', 'password', 'password_confirmation'],
    properties: [
        new OA\Property(property: 'current_password', type: 'string', format: 'password'),
        new OA\Property(property: 'password', type: 'string', format: 'password'),
        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
    ]
)]
final class UpdateUserPasswordRequestBody {}
