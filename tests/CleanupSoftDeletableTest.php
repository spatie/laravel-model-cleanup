<?php

namespace Tests;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Event;
use function Pest\Laravel\artisan;
use function PHPUnit\Framework\assertEquals;
use Spatie\ModelCleanup\CleanupConfig;
use Spatie\ModelCleanup\Commands\CleanUpModelsCommand;
use Spatie\ModelCleanup\Events\ModelCleanedUpEvent;
use Spatie\ModelCleanup\Exceptions\CleanupFailed;
use Spatie\TestTime\TestTime;
use Tests\Models\TestSoftDeletableModel;

beforeEach(function () {
    Event::fake();

    TestSoftDeletableModelFactory::new()
        ->startingFrom(now()->addDays(3))
        ->forPreviousDays(10)
        ->create();
});

it('can force delete models that use the SoftDeletes trait', function () {
    useCleanupConfig(function (CleanupConfig $cleanupConfig) {
        $cleanupConfig->olderThanDays(2);
    }, TestSoftDeletableModel::class);

    artisan(CleanUpModelsCommand::class)->assertExitCode(0);

    assertModelsExistForDays([
        '2020-01-03',
        '2020-01-02',
        '2020-01-01',
        '2019-12-31',
        '2019-12-30',
    ], TestSoftDeletableModel::class);

    TestSoftDeletableModel::whereDate('created_at', '<', '2020-01-01')->delete();

    assertDeletedModelsExistForDays([
        '2019-12-31',
        '2019-12-30',
    ], TestSoftDeletableModel::class);

    artisan(CleanUpModelsCommand::class)->assertExitCode(0);
});
