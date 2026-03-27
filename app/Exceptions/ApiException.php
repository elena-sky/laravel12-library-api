<?php

namespace App\Exceptions;

use Exception;
use Throwable;

/**
 * Base for deliberate API failures: mapped in bootstrap to a stable JSON body and HTTP status,
 * unlike unexpected exceptions that become a generic 500 for clients.
 */
class ApiException extends Exception
{
    /**
     * Carries HTTP semantics for intentional failures handled by the API exception renderer.
     *
     * @param  array<string, list<string>>  $errors  Optional field-level messages, same shape as validation errors
     */
    public function __construct(
        string $message = '',
        protected int $statusCode = 400,
        protected array $errors = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * HTTP status code used when rendering this exception to JSON.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Field errors for clients; empty when the failure is message-only.
     *
     * @return array<string, list<string>>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
