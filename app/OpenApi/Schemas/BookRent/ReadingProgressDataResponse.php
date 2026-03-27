<?php

namespace App\OpenApi\Schemas\BookRent;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ReadingProgressDataResponse',
    required: ['data'],
    properties: [
        new OA\Property(
            property: 'data',
            properties: [
                new OA\Property(property: 'reading_progress', type: 'integer', minimum: 0, maximum: 100, example: 42),
            ],
            type: 'object'
        ),
    ]
)]
final class ReadingProgressDataResponse {}
