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

use Carbon\CarbonInterface;
use Closure;
use Illuminate\Database\Query\Builder;

class CleanupConfig
{
    public ?CarbonInterface $olderThan = null;

    public ?Closure $scope = null;

    public ?int $chunkBy = null;

    public Closure $continueWhile;

    public function __construct()
    {
        $this->continueWhile = function () {
            return false;
        };
    }

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

    public function scope(Closure $scopeClosure): self
    {
        $this->scope = $scopeClosure;

        return $this;
    }

    public function chunk(int $chunkBy, Closure $continueWhile = null): self
    {
        $this->chunkBy = $chunkBy;

        $this->continueWhile = $continueWhile ?? fn (int $numberOfRecordsDeleted) => $numberOfRecordsDeleted >= $chunkBy;

        if ($continueWhile) {
            $this->continueWhile = $continueWhile;
        }

        return $this;
    }
}
