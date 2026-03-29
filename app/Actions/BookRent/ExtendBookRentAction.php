<?php

namespace App\Actions\BookRent;

use App\Exceptions\ResourceConflictException;
use App\Models\BookRent;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

final class ExtendBookRentAction
{
    public function execute(BookRent $rent, CarbonInterface $newDueDate): BookRent
    {
        return DB::transaction(static function () use ($rent, $newDueDate): BookRent {
            /** @var BookRent $locked */
            $locked = BookRent::query()->whereKey($rent->getKey())->lockForUpdate()->firstOrFail();

            if (! $locked->isActive()) {
                throw new ResourceConflictException('Cannot extend a finished rental');
            }

            $locked->due_date = $newDueDate;
            $locked->extended_count = $locked->extended_count + 1;
            $locked->save();

            return $locked->refresh();
        });
    }
}
