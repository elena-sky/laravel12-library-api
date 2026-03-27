<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

/**
 * Root OpenAPI metadata; operations are discovered from attributed HTTP contracts under app/Http/Contracts (and implementations).
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
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    description: 'Laravel Sanctum personal access token: Authorization: Bearer {token}'
)]
final class OpenApiInfo {}
