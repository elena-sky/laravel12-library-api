<?php

namespace App\Http\Requests\BookRent;

use App\Models\BookRent;
use Illuminate\Foundation\Http\FormRequest;

class ShowBookRentReadingProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        $rent = $this->route('bookRent');
        if (! $rent instanceof BookRent) {
            return false;
        }

        return $this->user()->can('view', $rent);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
