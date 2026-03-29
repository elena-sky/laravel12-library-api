<?php

namespace App\Http\Requests\Book;

use App\Models\Book;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Book::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'genre' => ['required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'total_copies' => ['required', 'integer', 'min:0'],
            'available_copies' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('available_copies') && $this->has('total_copies')) {
            $this->merge([
                'available_copies' => (int) $this->input('total_copies'),
            ]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $total = (int) $this->input('total_copies');
            $avail = (int) $this->input('available_copies', $total);
            if ($avail > $total) {
                $v->errors()->add('available_copies', 'Available copies cannot exceed total copies.');
            }
        });
    }
}
