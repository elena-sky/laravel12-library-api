<?php

namespace App\OpenApi\Schemas\BookRent;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RentBookRequestBody',
    required: ['book_id', 'due_date'],
    properties: [
        new OA\Property(property: 'book_id', type: 'integer', example: 1),
        new OA\Property(property: 'due_date', type: 'string', format: 'date-time'),
    ]
)]
final class RentBookRequestBody {}
