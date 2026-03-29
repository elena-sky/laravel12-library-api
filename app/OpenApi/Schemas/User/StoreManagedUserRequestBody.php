<?php

namespace App\OpenApi\Schemas\User;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'StoreManagedUserRequestBody',
    required: ['name', 'email', 'password', 'password_confirmation'],
    properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'email', type: 'string', format: 'email'),
        new OA\Property(property: 'password', type: 'string', format: 'password'),
        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
    ]
)]
final class StoreManagedUserRequestBody {}
