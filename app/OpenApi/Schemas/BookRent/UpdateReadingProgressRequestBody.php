<?php

namespace App\OpenApi\Schemas\BookRent;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateReadingProgressRequestBody',
    required: ['reading_progress'],
    properties: [
        new OA\Property(property: 'reading_progress', type: 'integer', minimum: 0, maximum: 100),
    ]
)]
final class UpdateReadingProgressRequestBody {}
