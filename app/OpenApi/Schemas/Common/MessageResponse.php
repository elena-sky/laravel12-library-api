<?php

namespace App\OpenApi\Schemas\Common;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MessageResponse',
    required: ['message'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Book deleted'),
    ]
)]
final class MessageResponse {}
