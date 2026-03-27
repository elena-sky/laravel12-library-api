<?php

namespace App\OpenApi\Schemas\BookRent;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PaginatedBookRentsResponse',
    required: ['data', 'meta'],
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/BookRentResource')
        ),
        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
    ]
)]
final class PaginatedBookRentsResponse {}
