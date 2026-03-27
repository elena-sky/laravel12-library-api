<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

/**
 * Root OpenAPI metadata; operations are discovered from attributed interfaces/controllers under app/.
 */
#[OA\OpenApi(
    openapi: '3.0.0',
    info: new OA\Info(
        version: '1.0.0',
        title: 'Library API',
        description: 'REST API for the library system (versioned under /api/v1).'
    ),
    servers: [
        new OA\Server(url: '/api/v1', description: 'Version 1'),
    ]
)]
final class OpenApiInfo {}
