<?php

namespace Spatie\ModelCleanup;

use Illuminate\Console\Command;

class CleanUpModelsCommand extends Command
{
    protected $signature = 'clean:models';

    protected $description = 'Clean up models.';

    public function handle()
    {
        $this->comment('Start cleaning models...');

        collect(config('model-cleanup.models'))
            ->map(fn (string $className) => new $className)
            ->each(fn (GetsCleanedUp $model) => $this->cleanUp($model));

        $this->comment('All done!');
    }

    protected function cleanUp(GetsCleanedUp $model)
    {
        $modelClass = get_class($model);

        $this->info("Cleaning {$modelClass}...");

        $cleanupConfig = new CleanupConfig();

        $model->cleanUp($cleanupConfig);

        $query = $model::query();

        if ($cleanupConfig->olderThan) {
            $query->where('created_at', '<', $cleanupConfig->olderThan->toDateTimeString());
        }

        if ($cleanupConfig->scopeClosure) {
            ($cleanupConfig->scopeClosure)($query);
        }

        $numberOfDeletedRecords = $query->delete();

        event(new ModelCleanedUpEvent($model, $numberOfDeletedRecords));

        $this->info("Deleted {$numberOfDeletedRecords} record(s) from {$modelClass}.");
    }
}
