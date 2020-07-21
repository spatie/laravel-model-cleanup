<?php

namespace Spatie\ModelCleanup;

use Illuminate\Console\Command;
use Spatie\ModelCleanup\CleanupConfig\CleanupConfigFactory;

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

        /** @var CleanupConfigFactory $cleanupConfigFactory */
        $cleanupConfigFactory = app(CleanupConfigFactory::class);

        $cleanupConfig = $cleanupConfigFactory->getCleanupConfig();

        $model->cleanUp($cleanupConfig);

        do {
            $query = $model::query();

            if ($cleanupConfig->olderThan) {
                $query->where('created_at', '<', $cleanupConfig->olderThan->toDateTimeString());
            }

            if ($cleanupConfig->scope) {
                ($cleanupConfig->scope)($query);
            }

            if ($cleanupConfig->chunkBy) {
                $query->limit($cleanupConfig->chunkBy);
            }

            $numberOfDeletedRecords = $query->delete();

            $shouldContinueDeleting = $this->shouldContinueDeleting(
                $numberOfDeletedRecords,
                $cleanupConfig->continueWhile
            );
        } while ($shouldContinueDeleting);

        /** TODO: number of deleted record should be the total number of deletion records (sum of loops) + add test */
        event(new ModelCleanedUpEvent($model, $numberOfDeletedRecords));

        $this->info("Deleted {$numberOfDeletedRecords} record(s) from {$modelClass}.");
    }

    protected function shouldContinueDeleting(int $numberOfRecordDeleted, \Closure $continueWhile): bool
    {
        if (! $continueWhile($numberOfRecordDeleted)) {
            return false;
        }

        return $numberOfRecordDeleted !== 0;
    }
}
