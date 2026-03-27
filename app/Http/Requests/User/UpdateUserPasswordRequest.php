<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UpdateUserPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $v): void {
            $user = $this->user();
            if (! $user instanceof User) {
                return;
            }

            if (! Hash::check($this->string('current_password')->value(), $user->getAuthPassword())) {
                $v->errors()->add(
                    'current_password',
                    __('The provided password does not match your current password.'),
                );
            }
        });
    }
}
