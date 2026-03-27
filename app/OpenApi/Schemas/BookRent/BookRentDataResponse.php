<?php

namespace App\OpenApi\Schemas\BookRent;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BookRentDataResponse',
    required: ['data'],
    properties: [
        new OA\Property(property: 'data', ref: '#/components/schemas/BookRentResource'),
        new OA\Property(property: 'message', type: 'string', example: 'Rental extended'),
    ]
)]
final class BookRentDataResponse {}
