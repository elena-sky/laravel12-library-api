<?php

namespace App\DTO\User;

final readonly class UpdateUserData
{
    /**
     * @param  array<string, mixed>  $attributes  Only keys present in validated input (partial patch).
     */
    private function __construct(
        private array $attributes,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self($validated);
    }

    /**
     * @return array{name?: string, email?: string}
     */
    public function toUpdateArray(): array
    {
        $data = [];

        if (array_key_exists('name', $this->attributes)) {
            $data['name'] = (string) $this->attributes['name'];
        }

        if (array_key_exists('email', $this->attributes)) {
            $data['email'] = (string) $this->attributes['email'];
        }

        return $data;
    }

    public function isEmpty(): bool
    {
        return $this->toUpdateArray() === [];
    }
}
