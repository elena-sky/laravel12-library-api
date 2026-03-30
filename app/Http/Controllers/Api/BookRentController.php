<?php

namespace App\Http\Controllers\Api;

use App\Actions\BookRent\ExtendBookRentAction;
use App\Actions\BookRent\FinishBookRentAction;
use App\Actions\BookRent\ListBookRentsAction;
use App\Actions\BookRent\RentBookAction;
use App\Actions\BookRent\UpdateReadingProgressAction;
use App\Exceptions\ResourceConflictException;
use App\Http\Contracts\BookRentControllerInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\BookRent\ExtendBookRentRequest;
use App\Http\Requests\BookRent\FinishBookRentRequest;
use App\Http\Requests\BookRent\ListBookRentsRequest;
use App\Http\Requests\BookRent\StoreBookRentRequest;
use App\Http\Requests\BookRent\UpdateBookRentReadingProgressRequest;
use App\Http\Requests\BookRent\ShowBookRentRequest;
use App\Http\Resources\BookRentResource;
use App\Models\BookRent;
use App\Models\User;
use App\OpenApi\Schemas\BookRent\ReadingProgressDataResponse;
use App\Providers\AppServiceProvider;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * {@inheritDoc}
 *
 * {@link BookRentControllerInterface} documents scoped `{bookRent}` resolution; see
 * {@see AppServiceProvider::boot()} for the binding implementation.
 */
class BookRentController extends Controller implements BookRentControllerInterface
{
    public function __construct(
        private readonly ListBookRentsAction $listBookRents,
        private readonly RentBookAction $rentBook,
        private readonly ExtendBookRentAction $extendBookRent,
        private readonly UpdateReadingProgressAction $updateReadingProgress,
        private readonly FinishBookRentAction $finishBookRent,
    ) {}

    public function index(ListBookRentsRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $paginator = $this->listBookRents->execute($user, $request->perPage());

        return ApiResponse::paginated($paginator, BookRentResource::collection($paginator));
    }

    /**
     * @throws ResourceConflictException
     */
    public function store(StoreBookRentRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $data = $request->validated();

        $rent = $this->rentBook->execute(
            $user,
            (int) $data['book_id'],
            $request->dueDate()
        );
        $rent->load('book');

        return ApiResponse::resource(BookRentResource::make($rent), 'Rental started', 201);
    }

    public function show(ShowBookRentRequest $request, BookRent $bookRent): JsonResponse
    {
        $bookRent->load('book');

        return ApiResponse::resource(BookRentResource::make($bookRent));
    }

    public function extend(ExtendBookRentRequest $request, BookRent $bookRent): JsonResponse
    {
        $updated = $this->extendBookRent->execute($bookRent, $request->dueDate());
        $updated->load('book');

        return ApiResponse::resource(BookRentResource::make($updated), 'Rental extended');
    }

    /**
     * Intentionally returns a narrow payload (`reading_progress` only), matching
     * {@see ReadingProgressDataResponse}.
     */
    public function showReadingProgress(ShowBookRentRequest $request, BookRent $bookRent): JsonResponse
    {
        return ApiResponse::success([
            'reading_progress' => $bookRent->reading_progress,
        ]);
    }

    /**
     * @throws ResourceConflictException
     */
    public function updateReadingProgress(
        UpdateBookRentReadingProgressRequest $request,
        BookRent $bookRent
    ): JsonResponse {
        $progress = (int) $request->validated()['reading_progress'];
        $updated = $this->updateReadingProgress->execute($bookRent, $progress);
        $updated->load('book');

        return ApiResponse::resource(BookRentResource::make($updated), 'Reading progress updated');
    }

    /**
     * @throws ResourceConflictException
     */
    public function finish(FinishBookRentRequest $request, BookRent $bookRent): JsonResponse
    {
        $finished = $this->finishBookRent->execute($bookRent);
        $finished->load('book');

        return ApiResponse::resource(BookRentResource::make($finished), 'Rental finished');
    }
}
