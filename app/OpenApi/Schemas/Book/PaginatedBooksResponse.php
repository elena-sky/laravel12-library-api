<?php

namespace App\OpenApi\Schemas\Book;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PaginatedBooksResponse',
    required: ['data', 'meta'],
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/BookResource')
        ),
        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
    ]
)]
final class PaginatedBooksResponse {}
