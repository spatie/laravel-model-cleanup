<?php

namespace Tests;

use Carbon\Carbon;
use Tests\Models\TestModel;
use Tests\Models\TestSoftDeletableModel;

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
        $createdAt = $this->startingFrom;

        foreach (range(1, $this->numberOfDays) as $i) {
            $date = $createdAt->subDay();
            TestModel::create(['created_at' => $date]);
            TestSoftDeletableModel::create(['created_at' => $date]);
        }
    }
}
