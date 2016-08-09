<?php

namespace Spatie\ModelCleanup;

use Illuminate\Queue\SerializesModels;

class ModelCleanedEvent
{

    /**
     * Holds the model class name.
     * @var string
     */
    public $modelName;

    /**
     * Holds the number of deleted records for the model.
     * @var int
     */
    public $numberOfDeletedRecords;

    /**
     * Create a new event instance.
     *
     * @param  string  $modelName
     * @param  int  $numberOfDeletedRecords
     * @return void
     */
    public function __construct(string $modelName, int $numberOfDeletedRecords)
    {
        $this->modelName = $modelName;
        $this->numberOfDeletedRecords = $numberOfDeletedRecords;
    }
}