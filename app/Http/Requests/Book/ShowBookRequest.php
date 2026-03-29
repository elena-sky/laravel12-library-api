<?php

namespace App\Http\Requests\Book;

use App\Models\Book;
use Illuminate\Foundation\Http\FormRequest;

class ShowBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        $book = $this->route('book');
        if (! $book instanceof Book) {
            return false;
        }

        return $this->user()->can('view', $book);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
