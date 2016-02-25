<?php

namespace Spatie\DatabaseCleanup;

use Illuminate\Console\Command;

class CleanUpModelsCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'db:deleteExpiredRecords';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Delete all expired records from all chosen tables.";

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $this->getAllModelClassNames();

        Log::info('old records deleted');

    }

    private function getAllModelClassNames()
    {
        $modelClassNames = config('laravel-database-cleanup.models');

        foreach($modelClassNames as $modelClassName){
            $this->deleteExpiredRecordsForModelClass($modelClassName);
        }
    }

    private function deleteExpiredRecordsForModelClass($expiredModelClass)
    {
        $model = new $expiredModelClass();
        foreach ($model->cleanUpModels(365) as $expiredRecord) {
            $expiredRecord->delete();
        }
    }

}