<?php

namespace Tests;

use Carbon\Carbon;
use Tests\Models\TestModel;

class TestModelFactory
{
    protected ?Carbon $startingFrom = null;

    protected int $numberOfDays = 0;

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
        $createdAt = $this->startingFrom;

        foreach (range(1, $this->numberOfDays) as $i) {
            TestModel::create(['created_at' => $createdAt->subDay()]);
        }
    }
}
