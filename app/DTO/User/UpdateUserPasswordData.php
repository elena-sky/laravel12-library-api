<?php

namespace App\DTO\User;

final readonly class UpdateUserPasswordData
{
    public function __construct(
        public string $password,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(password: (string) $validated['password']);
    }
}
