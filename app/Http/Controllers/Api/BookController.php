<?php

namespace App\Http\Controllers\Api;

use App\Actions\Book\CreateBookAction;
use App\Actions\Book\DeleteBookAction;
use App\Actions\Book\ListBooksAction;
use App\Actions\Book\UpdateBookAction;
use App\Http\Contracts\BookControllerInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Book\DeleteBookRequest;
use App\Http\Requests\Book\ListBooksRequest;
use App\Http\Requests\Book\ShowBookRequest;
use App\Http\Requests\Book\StoreBookRequest;
use App\Http\Requests\Book\UpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Override;

/**
 * {@inheritDoc}
 */
class BookController extends Controller implements BookControllerInterface
{
    public function __construct(
        private readonly ListBooksAction $listBooks,
        private readonly CreateBookAction $createBook,
        private readonly UpdateBookAction $updateBook,
        private readonly DeleteBookAction $deleteBook,
    ) {}

    #[Override]
    public function index(ListBooksRequest $request): JsonResponse
    {
        $paginator = $this->listBooks->execute($request->filtersForAction());

        return ApiResponse::paginated($paginator, BookResource::collection($paginator));
    }

    #[Override]
    public function store(StoreBookRequest $request): JsonResponse
    {
        $book = $this->createBook->execute($request->validated());

        return ApiResponse::resource(BookResource::make($book), 'Book created', 201);
    }

    #[Override]
    public function show(ShowBookRequest $request, Book $book): JsonResponse
    {
        return ApiResponse::resource(BookResource::make($book));
    }

    #[Override]
    public function update(UpdateBookRequest $request, Book $book): JsonResponse
    {
        $updated = $this->updateBook->execute($book, $request->validated());

        return ApiResponse::resource(BookResource::make($updated), 'Book updated');
    }

    #[Override]
    public function destroy(DeleteBookRequest $request, Book $book): JsonResponse
    {
        $this->deleteBook->execute($book);

        return ApiResponse::success(null, 'Book deleted');
    }
}
