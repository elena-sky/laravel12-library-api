<?php

namespace App\Exceptions;

use Throwable;

/**
 * Request is valid but conflicts with current data (duplicates, concurrent state);
 * HTTP 409 distinguishes this from validation rule violations.
 */
class ResourceConflictException extends ApiException
{
    /**
     * Valid request conflicting with current persisted state (HTTP 409).
     *
     * @param  array<string, list<string>>  $errors
     */
    public function __construct(
        string $message = 'Resource conflict',
        array $errors = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 409, $errors, $previous);
    }
}
