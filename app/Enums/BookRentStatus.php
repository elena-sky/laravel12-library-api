<?php

namespace App\Enums;

/**
 * Rental lifecycle states (block3 §4.1). Overdue is derived from active + due_date, not stored.
 */
enum BookRentStatus: string
{
    case Active = 'active';
    case Finished = 'finished';
}
