<?php

namespace Spatie\ModelCleanup\Commands;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Spatie\ModelCleanup\CleanupConfig;
use Spatie\ModelCleanup\Events\ModelCleanedUpEvent;
use Spatie\ModelCleanup\Exceptions\InvalidCleanupConfig;
use Spatie\ModelCleanup\GetsCleanedUp;

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

        if (! $cleanupConfig->isValid()) {
            throw InvalidCleanupConfig::create($model);
        }

        $totalNumberOfDeletedRecords = 0;

        do {
            $query = $model::query();

            if ($cleanupConfig->olderThan) {
                $query->where($cleanupConfig->dateAttribute, '<', $cleanupConfig->olderThan->toDateTimeString());
            }

            if ($cleanupConfig->scope) {
                ($cleanupConfig->scope)($query);
            }

            if ($cleanupConfig->chunkBy) {
                $query->limit($cleanupConfig->chunkBy);
            }

            $numberOfDeletedRecords = $query->delete();

            $totalNumberOfDeletedRecords += $numberOfDeletedRecords;

            $shouldContinueDeleting = $this->shouldContinueDeleting(
                $numberOfDeletedRecords,
                $cleanupConfig->continueWhile
            );
        } while ($shouldContinueDeleting);

        event(new ModelCleanedUpEvent($model, $totalNumberOfDeletedRecords));

        $this->info('Deleted ' . $totalNumberOfDeletedRecords . ' ' . Str::plural('record', $totalNumberOfDeletedRecords) .   " from {$modelClass}.");
    }

    protected function shouldContinueDeleting(int $numberOfRecordDeleted, Closure $continueWhile): bool
    {
        if (! $continueWhile($numberOfRecordDeleted)) {
            return false;
        }

        return $numberOfRecordDeleted !== 0;
    }
}
