<?php

namespace App\OpenApi\Schemas\BookRent;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ExtendRentRequestBody',
    required: ['due_date'],
    properties: [
        new OA\Property(property: 'due_date', type: 'string', format: 'date-time'),
    ]
)]
final class ExtendRentRequestBody {}
