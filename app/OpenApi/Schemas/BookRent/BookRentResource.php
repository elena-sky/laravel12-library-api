<?php

namespace App\OpenApi\Schemas\BookRent;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BookRentResource',
    required: [
        'id', 'book_id', 'status', 'rented_at', 'due_date', 'reading_progress',
        'extended_count', 'created_at', 'updated_at',
    ],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'book_id', type: 'integer'),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'finished']),
        new OA\Property(property: 'rented_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'due_date', type: 'string', format: 'date-time'),
        new OA\Property(property: 'returned_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'reading_progress', type: 'integer', minimum: 0, maximum: 100),
        new OA\Property(property: 'extended_count', type: 'integer', minimum: 0),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'book', ref: '#/components/schemas/BookResource', nullable: true),
    ]
)]
final class BookRentResource {}
