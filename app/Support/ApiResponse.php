<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Centralizes the API JSON envelope so controllers and the exception renderer stay aligned
 * with one contract (data / meta / message) instead of ad-hoc arrays.
 */
final class ApiResponse
{
    /**
     * Success body with optional `data` and `message`; omits absent parts from the payload.
     */
    public static function success(mixed $data = null, ?string $message = null, int $status = 200): JsonResponse
    {
        $payload = [];

        if ($data !== null) {
            $payload['data'] = $data;
        }

        if ($message !== null) {
            $payload['message'] = $message;
        }

        return response()->json($payload, $status);
    }

    /**
     * Same as {@see success} with HTTP 201 for created resources.
     */
    public static function created(mixed $data = null, ?string $message = null): JsonResponse
    {
        return self::success($data, $message, 201);
    }

    /**
     * Minimal body when only a human-readable message is required (e.g. errors without `data`).
     */
    public static function plainMessage(string $message, int $status = 200): JsonResponse
    {
        return response()->json(['message' => $message], $status);
    }

    /**
     * Error body; `$errors` matches Laravel validation shape when present.
     *
     * @param  array<string, list<string>>  $errors
     */
    public static function error(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        $payload = ['message' => $message];

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    /**
     * JSON envelope with `data` from a {@see JsonResource} and optional `message` (same shape as {@see success}).
     */
    public static function resource(JsonResource $resource, ?string $message = null, int $status = 200): JsonResponse
    {
        return self::success(self::materializeResource($resource), $message, $status);
    }

    /**
     * Resolved resource payload for embedding inside a larger `data` object (e.g. registration with `user` + `token`).
     */
    public static function resourceData(JsonResource $resource): mixed
    {
        return self::materializeResource($resource);
    }

    /**
     * Single call site for {@see JsonResource::resolve()} in this class.
     */
    private static function materializeResource(JsonResource $resource): mixed
    {
        return $resource->resolve();
    }

    /**
     * Paginated collection plus paging meta; map items with a resource, callback, or default `toArray`.
     *
     * @param  ResourceCollection|(callable(LengthAwarePaginator): mixed)|null  $transformer
     */
    public static function paginated(
        LengthAwarePaginator $paginator,
        ResourceCollection|callable|null $transformer = null
    ): JsonResponse {
        if ($transformer instanceof ResourceCollection) {
            $data = $transformer->resolve();
        } elseif (is_callable($transformer)) {
            $data = $transformer($paginator);
        } else {
            $data = array_map(
                static fn ($item) => is_object($item) && method_exists($item, 'toArray') ? $item->toArray() : $item,
                $paginator->items()
            );
        }

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }
}
