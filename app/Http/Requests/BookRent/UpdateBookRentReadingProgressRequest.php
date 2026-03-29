<?php

namespace App\Http\Requests\BookRent;

use App\Models\BookRent;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRentReadingProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        $rent = $this->route('bookRent');
        if (! $rent instanceof BookRent) {
            return false;
        }

        return $this->user()->can('update', $rent);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reading_progress' => ['required', 'integer', 'min:0', 'max:100'],
        ];
    }
}
