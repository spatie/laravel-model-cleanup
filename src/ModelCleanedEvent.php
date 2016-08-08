<?php

namespace Spatie\ModelCleanup;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class ModelCleanedEvent extends Event
{

    public $modelName;
    public $numberOfDeletedRecords;

    /**
     * Create a new event instance.
     *
     * @param  string  $modelName
     * @param  int  $deletedRecords
     * @return void
     */
    public function __construct(string $modelName, int $numberOfDeletedRecords)
    {
        $this->modelName = $modelName;
        $this->numberOfDeletedRecords = $numberOfDeletedRecords;
    }
}