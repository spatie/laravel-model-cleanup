<?php

namespace Spatie\ModelCleanup\Test;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Spatie\ModelCleanup\Test\Models\TestModel;

class TestModelFactory
{
    private ?Carbon $startingFrom = null;

    private int $numberOfDays = 0;

    public static function new(): self
    {
        return new static();
    }

    public function startingFrom(Carbon $startingFrom): self
    {
        $this->startingFrom = $startingFrom;

        return $this;
    }

    public function forPreviousDays(int $numberOfDays): self
    {
        $this->numberOfDays = $numberOfDays;

        return $this;
    }

    public function create()
    {
        CarbonPeriod::create($this->startingFrom->subDays($this->numberOfDays), $this->numberOfDays)
            ->forEach(fn(Carbon $created_at) => TestModel::create(compact('created_at')));
    }
}
