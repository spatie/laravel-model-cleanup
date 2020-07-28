<?php

namespace Tests;

use function Pest\Laravel\artisan;
use Spatie\ModelCleanup\CleanupConfig;
use Spatie\ModelCleanup\Commands\CleanUpModelsCommand;
use Tests\Models\TestSoftDeletableModel;

beforeEach(function () {
    TestSoftDeletableModelFactory::new()
        ->startingFrom(now()->addDays(3))
        ->forPreviousDays(10)
        ->create();
});

it('can delete old soft deletable records that are older than a given number of days', function () {
    useCleanupConfig(function (CleanupConfig $cleanupConfig) {
        $cleanupConfig->olderThanDays(2);
    }, TestSoftDeletableModel::class);

    TestSoftDeletableModel::whereDate('created_at', '<', '2020-01-01')->delete();

    artisan(CleanUpModelsCommand::class)->assertExitCode(0);

    assertModelsWithTrashedExistForDays([
        '2020-01-03',
        '2020-01-02',
        '2020-01-01',
        '2019-12-31',
        '2019-12-30',
    ], TestSoftDeletableModel::class);
});
