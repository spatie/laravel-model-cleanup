<?php

namespace Spatie\ModelCleanup;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class CleanUpModelsCommand extends Command
{
    protected $signature = 'clean:models';

    protected $description = 'Clean up models.';

    public function handle()
    {
        $this->comment('Cleaning models...');

        collect(config('model-cleanup.models'))
            ->map(function (string $className) {
                return new $className;
            })
            ->each(function (GetsCleanedUp $model) {
                // delete stuff
            });


        $this->comment('All done!');
    }

    protected function cleanUp(Collection $cleanableModels)
    {
        $cleanableModels->each(function (string $modelClass) {
            $numberOfDeletedRecords = $modelClass::cleanUp($modelClass::query())->delete();

            event(new ModelCleanedUp($modelClass, $numberOfDeletedRecords));

            $this->info("Deleted {$numberOfDeletedRecords} record(s) from {$modelClass}.");
        });
    }
}
