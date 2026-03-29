<?php

namespace App\Http\Requests\Book;

use App\Models\Book;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        $book = $this->route('book');
        if (! $book instanceof Book) {
            return false;
        }

        return $this->user()->can('update', $book);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'author' => ['sometimes', 'string', 'max:255'],
            'genre' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'total_copies' => ['sometimes', 'integer', 'min:0'],
            'available_copies' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $book = $this->route('book');
            if (! $book instanceof Book) {
                return;
            }

            $book->refresh();

            $total = $this->has('total_copies')
                ? (int) $this->input('total_copies')
                : (int) $book->total_copies;
            $avail = $this->has('available_copies')
                ? (int) $this->input('available_copies')
                : (int) $book->available_copies;

            if ($avail > $total) {
                $v->errors()->add('available_copies', 'Available copies cannot exceed total copies.');
            }

            if ($total < $book->available_copies && ! $this->has('available_copies')) {
                $v->errors()->add(
                    'total_copies',
                    'Total copies cannot be less than current available copies without adjusting available copies.'
                );
            }

            if ($this->has('total_copies') && $total < $avail) {
                $v->errors()->add('total_copies', 'Total copies cannot be less than available copies.');
            }
        });
    }
}
