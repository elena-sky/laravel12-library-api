<?php

namespace App\OpenApi\Schemas\Common;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PaginationMeta',
    required: ['current_page', 'per_page', 'total', 'last_page'],
    properties: [
        new OA\Property(property: 'current_page', type: 'integer'),
        new OA\Property(property: 'per_page', type: 'integer'),
        new OA\Property(property: 'total', type: 'integer'),
        new OA\Property(property: 'last_page', type: 'integer'),
    ]
)]
final class PaginationMeta {}
