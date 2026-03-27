<?php

namespace App\OpenApi\Schemas\Book;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BookDataResponse',
    required: ['data'],
    properties: [
        new OA\Property(property: 'data', ref: '#/components/schemas/BookResource'),
        new OA\Property(property: 'message', type: 'string', example: 'Book updated'),
    ]
)]
final class BookDataResponse {}
