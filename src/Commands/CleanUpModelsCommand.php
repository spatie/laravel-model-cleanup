<?php

namespace Spatie\ModelCleanup\Commands;

use Closure;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Spatie\ModelCleanup\CleanupConfig;
use Spatie\ModelCleanup\Events\ModelCleanedUpEvent;
use Spatie\ModelCleanup\Exceptions\CleanupFailed;
use Spatie\ModelCleanup\Exceptions\InvalidCleanupConfig;
use Spatie\ModelCleanup\GetsCleanedUp;

class CleanUpModelsCommand extends Command
{
    protected $signature = 'clean:models';

    protected $description = 'Clean up models.';

    public function handle()
    {
        $this->info('Start cleaning models...');

        $exceptions = [];

        collect(config('model-cleanup.models'))
            ->map(fn (string $className) => new $className)
            ->each(function (GetsCleanedUp $model) use (&$exceptions) {
                try {
                    $this->cleanUp($model);
                } catch (Exception $exception) {
                    $exceptions[] = compact('model', 'exception');

                    $this->error("Could not clean up model. Exception `" . get_class($exception) . "` occurred: {$exception->getMessage()}");
                }

                $this->info('');
            });

        if (count($exceptions)) {
            throw CleanupFailed::create($exceptions);
        }

        $this->info('All done!');
    }

    protected function cleanUp(GetsCleanedUp $model)
    {
        $modelClass = get_class($model);

        $this->comment("Cleaning {$modelClass}...");

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

            $numberOfDeletedRecords = (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses($model)) 
               ? $query->forceDelete()
               : $query->delete());

            $totalNumberOfDeletedRecords += $numberOfDeletedRecords;

            $shouldContinueDeleting = $this->shouldContinueDeleting(
                $numberOfDeletedRecords,
                $cleanupConfig->continueWhile
            );

            if ($shouldContinueDeleting) {
                $this->comment(' ' . $totalNumberOfDeletedRecords . ' ' . Str::plural('record', $totalNumberOfDeletedRecords) . " have been deleted for {$modelClass}. Finding more deletable records");
            }
        } while ($shouldContinueDeleting);

        event(new ModelCleanedUpEvent($model, $totalNumberOfDeletedRecords));

        $this->comment("{$modelClass} has been cleaned. Deleted " . $totalNumberOfDeletedRecords . ' ' . Str::plural('record', $totalNumberOfDeletedRecords) . " in total");
    }

    protected function shouldContinueDeleting(int $numberOfRecordDeleted, Closure $continueWhile): bool
    {
        if (! $continueWhile($numberOfRecordDeleted)) {
            return false;
        }

        return $numberOfRecordDeleted !== 0;
    }
}
