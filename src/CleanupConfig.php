<?php

namespace Spatie\ModelCleanup;

/*
 *         return (new CleanUp)
            ->olderThanDays(10)
            ->olderThan($carbon)

            ->query(function(Builder $query))
            ->chunk(10000)
            ->stopWhen(function() {});
 */

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class CleanupConfig
{
    public ?CarbonInterface $olderThan = null;

    /** TODO: consider adding parameter for column name */
    public function olderThanDays(int $numberOfDays): self
    {
        $this->olderThan = now()->subDays($numberOfDays);

        return $this;
    }

    public function olderThan(CarbonInterface $olderThan): self
    {
        $this->olderThan = $olderThan;

        return $this;
    }
}
