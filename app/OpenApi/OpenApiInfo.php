<?php

namespace App\OpenApi;

use App\OpenApi\Schemas\Book\BookDataResponse;
use App\OpenApi\Schemas\Book\BookResource;
use OpenApi\Attributes as OA;

/**
 * Root OpenAPI metadata; operations are discovered from attributed HTTP contracts under app/Http/Contracts.
 * Component schemas live under App\OpenApi\Schemas (imported here so static analysis and OpenAPI scan register them).
 *
 * @see BookResource
 * @see BookDataResponse
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
