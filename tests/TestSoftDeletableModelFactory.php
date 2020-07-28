<?php

namespace Tests;

use Tests\Models\TestSoftDeletableModel;

class TestSoftDeletableModelFactory extends TestModelFactory
{
    public function create()
    {
        $createdAt = $this->startingFrom;

        foreach (range(1, $this->numberOfDays) as $i) {
            TestSoftDeletableModel::create(['created_at' => $createdAt->subDay()]);
        }
    }
}
