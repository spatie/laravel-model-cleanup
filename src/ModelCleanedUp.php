<?php

namespace Spatie\ModelCleanup;

class ModelCleanedUp
{
    /**
     * Holds the model class name.
     *
     * @var string
     */
    public $modelClass;

    /**
     * Holds the number of deleted records for the model.
     *
     * @var int
     */
    public $numberOfDeletedRecords;

    public function __construct(string $modelClass, int $numberOfDeletedRecords)
    {
        $this->modelClass = $modelClass;

        $this->numberOfDeletedRecords = $numberOfDeletedRecords;
    }
}
