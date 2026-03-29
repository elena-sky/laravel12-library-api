<?php

namespace App\Http\Requests\BookRent;

use App\Models\BookRent;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Http\FormRequest;

class ExtendBookRentRequest extends FormRequest
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
            'due_date' => ['required', 'date', 'after:now'],
        ];
    }

    public function dueDate(): CarbonInterface
    {
        return Carbon::parse($this->validated('due_date'));
    }
}
