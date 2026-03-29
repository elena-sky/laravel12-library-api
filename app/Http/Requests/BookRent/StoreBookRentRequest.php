<?php

namespace App\Http\Requests\BookRent;

use App\Models\BookRent;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookRentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', BookRent::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'book_id' => ['required', 'integer'],
            'due_date' => ['required', 'date', 'after:now'],
        ];
    }

    public function dueDate(): CarbonInterface
    {
        return Carbon::parse($this->validated('due_date'));
    }
}
