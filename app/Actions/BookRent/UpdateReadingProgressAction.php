<?php

namespace App\Actions\BookRent;

use App\Exceptions\ResourceConflictException;
use App\Models\BookRent;
use Illuminate\Support\Facades\DB;

final class UpdateReadingProgressAction
{
    /**
     * @throws ResourceConflictException
     */
    public function execute(BookRent $rent, int $readingProgress): BookRent
    {
        return DB::transaction(function () use ($rent, $readingProgress): BookRent {
            /** @var BookRent $locked */
            $locked = BookRent::query()->whereKey($rent->getKey())->lockForUpdate()->firstOrFail();

            if (! $locked->isActive()) {
                throw new ResourceConflictException('Cannot update reading progress on a finished rental');
            }

            $locked->reading_progress = $readingProgress;
            $locked->save();

            return $locked->refresh();
        });
    }
}
