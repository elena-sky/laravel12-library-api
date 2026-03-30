<?php

namespace App\Http\Requests\Book;

use App\Actions\Book\ListBooksAction;
use App\Models\Book;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListBooksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Book::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'author' => ['sometimes', 'nullable', 'string', 'max:255'],
            'genre' => ['sometimes', 'nullable', 'string', 'max:255'],
            'available_only' => ['sometimes', 'boolean'],
            'sort_by' => ['sometimes', 'string', Rule::in(ListBooksAction::SORT_WHITELIST)],
            'sort_dir' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sort_by' => $this->input('sort_by', 'title'),
            'sort_dir' => $this->input('sort_dir', 'asc'),
            'per_page' => $this->input('per_page', 15),
            'page' => $this->input('page', 1),
        ]);
    }

    /**
     * @return array{
     *     title?: ?string,
     *     author?: ?string,
     *     genre?: ?string,
     *     available_only?: bool,
     *     sort_by: string,
     *     sort_dir: string,
     *     per_page: int,
     *     page: int
     * }
     */
    public function filtersForAction(): array
    {
        /** @var array<string, mixed> $v */
        $v = $this->validated();

        return [
            'title' => $v['title'] ?? null,
            'author' => $v['author'] ?? null,
            'genre' => $v['genre'] ?? null,
            'available_only' => (bool) ($v['available_only'] ?? false),
            'sort_by' => $v['sort_by'],
            'sort_dir' => $v['sort_dir'],
            'per_page' => (int) $v['per_page'],
            'page' => (int) $v['page'],
        ];
    }
}
