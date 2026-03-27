<?php

namespace App\OpenApi\Schemas\Book;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateBookRequestBody',
    properties: [
        new OA\Property(property: 'title', type: 'string'),
        new OA\Property(property: 'author', type: 'string'),
        new OA\Property(property: 'genre', type: 'string'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'total_copies', type: 'integer', minimum: 0),
        new OA\Property(property: 'available_copies', type: 'integer', minimum: 0),
    ]
)]
final class UpdateBookRequestBody {}
