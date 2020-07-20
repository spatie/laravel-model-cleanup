<?php

namespace Spatie\ModelCleanup;

class ModelCleanedUpEvent
{
    public GetsCleanedUp $model;

    public int $numberOfDeletedRecords;

    public function __construct(GetsCleanedUp $model, int $numberOfDeletedRecords)
    {
        $this->model = $model;

        $this->numberOfDeletedRecords = $numberOfDeletedRecords;
    }
}
