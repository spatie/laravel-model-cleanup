<?php

namespace Spatie\ModelCleanup\CleanupConfig;

use Carbon\CarbonInterface;
use Closure;

class CleanupConfig
{
    public ?CarbonInterface $olderThan = null;

    public ?Closure $scope = null;

    public ?int $chunkBy = null;

    public Closure $continueWhile;

    public string $dateAttribute = 'created_at';

    public static function new(): self
    {
        return new static();
    }

    public function __construct()
    {
        $this->continueWhile = function () {
            return false;
        };
    }

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

    public function useDateAttribute(string $dateAttribute): self
    {
        $this->dateAttribute = $dateAttribute;

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

        $this->continueWhile = $continueWhile
            ?? fn (int $numberOfRecordsDeleted) => $numberOfRecordsDeleted >= $chunkBy;

        return $this;
    }

    public function isValid(): bool
    {
        if (! is_null($this->olderThan)) {
            return true;
        }

        if (! is_null($this->scope)) {
            return true;
        }

        return false;
    }
}
