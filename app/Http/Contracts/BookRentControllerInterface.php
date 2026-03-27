<?php

namespace App\Http\Contracts;

use App\Http\Requests\BookRent\ExtendBookRentRequest;
use App\Http\Requests\BookRent\FinishBookRentRequest;
use App\Http\Requests\BookRent\ListBookRentsRequest;
use App\Http\Requests\BookRent\ShowBookRentReadingProgressRequest;
use App\Http\Requests\BookRent\ShowBookRentRequest;
use App\Http\Requests\BookRent\StoreBookRentRequest;
use App\Http\Requests\BookRent\UpdateBookRentReadingProgressRequest;
use App\Models\BookRent;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

/**
 * Current user rentals only; route binding scopes {bookRent} to owner.
 *
 * OpenAPI: `per_page` for GET /rentals — keep in sync with {@see ListBookRentsRequest}.
 */
interface BookRentControllerInterface
{
    #[OA\Get(
        path: '/rentals',
        operationId: 'rentalsIndex',
        summary: 'List my rentals',
        security: [['sanctum' => []]],
        tags: ['BookRent'],
        parameters: [
            // Keep in sync with ListBookRentsRequest (per_page rules)
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, default: 15)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated rentals',
                content: new OA\JsonContent(ref: '#/components/schemas/PaginatedBookRentsResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(ListBookRentsRequest $request): JsonResponse;

    #[OA\Post(
        path: '/rentals',
        operationId: 'rentalsStore',
        summary: 'Rent a book',
        security: [['sanctum' => []]],
        tags: ['BookRent'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/RentBookRequestBody')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Rental created',
                content: new OA\JsonContent(ref: '#/components/schemas/BookRentDataResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 409, description: 'Not available'),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
    public function store(StoreBookRentRequest $request): JsonResponse;

    #[OA\Get(
        path: '/rentals/{bookRent}',
        operationId: 'rentalsShow',
        summary: 'Get my rental',
        security: [['sanctum' => []]],
        tags: ['BookRent'],
        parameters: [
            new OA\Parameter(name: 'bookRent', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Rental',
                content: new OA\JsonContent(ref: '#/components/schemas/BookRentDataResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(ShowBookRentRequest $request, BookRent $bookRent): JsonResponse;

    #[OA\Patch(
        path: '/rentals/{bookRent}/extend',
        operationId: 'rentalsExtend',
        summary: 'Extend active rental',
        security: [['sanctum' => []]],
        tags: ['BookRent'],
        parameters: [
            new OA\Parameter(name: 'bookRent', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ExtendRentRequestBody')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Extended',
                content: new OA\JsonContent(ref: '#/components/schemas/BookRentDataResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 409, description: 'Not active'),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
    public function extend(ExtendBookRentRequest $request, BookRent $bookRent): JsonResponse;

    #[OA\Get(
        path: '/rentals/{bookRent}/reading-progress',
        operationId: 'rentalsReadingProgressShow',
        summary: 'Get reading progress',
        security: [['sanctum' => []]],
        tags: ['BookRent'],
        parameters: [
            new OA\Parameter(name: 'bookRent', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Progress',
                content: new OA\JsonContent(ref: '#/components/schemas/ReadingProgressDataResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function showReadingProgress(ShowBookRentReadingProgressRequest $request, BookRent $bookRent): JsonResponse;

    #[OA\Patch(
        path: '/rentals/{bookRent}/reading-progress',
        operationId: 'rentalsReadingProgressUpdate',
        summary: 'Update reading progress (active only)',
        security: [['sanctum' => []]],
        tags: ['BookRent'],
        parameters: [
            new OA\Parameter(name: 'bookRent', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateReadingProgressRequestBody')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated',
                content: new OA\JsonContent(ref: '#/components/schemas/BookRentDataResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 409, description: 'Finished rental'),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
    public function updateReadingProgress(UpdateBookRentReadingProgressRequest $request, BookRent $bookRent): JsonResponse;

    #[OA\Post(
        path: '/rentals/{bookRent}/finish',
        operationId: 'rentalsFinish',
        summary: 'Finish rental and return copy',
        security: [['sanctum' => []]],
        tags: ['BookRent'],
        parameters: [
            new OA\Parameter(name: 'bookRent', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Finished',
                content: new OA\JsonContent(ref: '#/components/schemas/BookRentDataResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 409, description: 'Already finished'),
        ]
    )]
    public function finish(FinishBookRentRequest $request, BookRent $bookRent): JsonResponse;
}
