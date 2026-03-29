<?php

namespace App\Http\Requests\BookRent;

use App\Models\BookRent;
use Illuminate\Foundation\Http\FormRequest;

class ListBookRentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', BookRent::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'per_page' => $this->input('per_page', 15),
        ]);
    }

    public function perPage(): int
    {
        return (int) $this->validated()['per_page'];
    }
}
