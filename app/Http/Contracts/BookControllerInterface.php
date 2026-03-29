<?php

namespace App\Http\Contracts;

use App\Actions\Book\ListBooksAction;
use App\Http\Requests\Book\ListBooksRequest;
use App\Http\Requests\Book\StoreBookRequest;
use App\Http\Requests\Book\UpdateBookRequest;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

/**
 * Catalog CRUD and listing (authenticated). Filters/sort whitelist match block3 §1.2.
 *
 * List search uses case-insensitive SQL substring match (LIKE), not full-text search — assignment trade-off.
 * OpenAPI query params: {@see ListBooksAction::SORT_WHITELIST} for `sort_by` enum;
 * defaults `sort_by=title`, `sort_dir=asc`, `per_page=15` — {@see ListBooksRequest}.
 */
interface BookControllerInterface
{
    #[OA\Get(
        path: '/books',
        operationId: 'booksIndex',
        summary: 'List and search books',
        description: 'Filter fields match case-insensitive substrings (SQL LIKE), not full-text search. Default sort: title asc; per_page default 15, max 100.',
        security: [['sanctum' => []]],
        tags: ['Book'],
        parameters: [
            new OA\Parameter(name: 'title', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'author', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'genre', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(
                name: 'available_only',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'boolean'),
                example: true
            ),
            // Keep in sync with ListBooksAction::SORT_WHITELIST
            new OA\Parameter(
                name: 'sort_by',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['title', 'author', 'genre', 'created_at', 'available_copies', 'total_copies'],
                    default: 'title'
                )
            ),
            new OA\Parameter(
                name: 'sort_dir',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], default: 'asc')
            ),
            // Keep in sync with ListBooksRequest (per_page rules)
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
                description: 'Paginated books',
                content: new OA\JsonContent(ref: '#/components/schemas/PaginatedBooksResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
    public function index(ListBooksRequest $request): JsonResponse;

    #[OA\Post(
        path: '/books',
        operationId: 'booksStore',
        summary: 'Create book',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/StoreBookRequestBody')
        ),
        tags: ['Book'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Created',
                content: new OA\JsonContent(ref: '#/components/schemas/BookDataResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
    public function store(StoreBookRequest $request): JsonResponse;

    #[OA\Get(
        path: '/books/{book}',
        operationId: 'booksShow',
        summary: 'Get book by id',
        security: [['sanctum' => []]],
        tags: ['Book'],
        parameters: [
            new OA\Parameter(name: 'book', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Book',
                content: new OA\JsonContent(ref: '#/components/schemas/BookDataResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(Book $book): JsonResponse;

    #[OA\Patch(
        path: '/books/{book}',
        operationId: 'booksUpdate',
        summary: 'Update book',
        security: [['sanctum' => []]],
        tags: ['Book'],
        parameters: [
            new OA\Parameter(name: 'book', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateBookRequestBody')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated',
                content: new OA\JsonContent(ref: '#/components/schemas/BookDataResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 409, description: 'Conflict'),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
    public function update(UpdateBookRequest $request, Book $book): JsonResponse;

    #[OA\Delete(
        path: '/books/{book}',
        operationId: 'booksDestroy',
        summary: 'Delete book (forbidden if active rentals exist)',
        security: [['sanctum' => []]],
        tags: ['Book'],
        parameters: [
            new OA\Parameter(name: 'book', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Deleted',
                content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 409, description: 'Active rentals'),
        ]
    )]
    public function destroy(Book $book): JsonResponse;
}
