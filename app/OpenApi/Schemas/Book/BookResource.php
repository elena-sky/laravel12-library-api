<?php

namespace App\OpenApi\Schemas\Book;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BookResource',
    required: ['id', 'title', 'author', 'genre', 'total_copies', 'available_copies', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string'),
        new OA\Property(property: 'author', type: 'string'),
        new OA\Property(property: 'genre', type: 'string'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'total_copies', type: 'integer'),
        new OA\Property(property: 'available_copies', type: 'integer'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
final class BookResource {}
