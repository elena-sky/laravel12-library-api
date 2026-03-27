<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Shared HTTP base: policy checks via {@see AuthorizesRequests} without pulling in unrelated traits.
 */
abstract class Controller
{
    use AuthorizesRequests;
}
