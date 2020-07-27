<?php

namespace Tests;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Event;
use function Pest\Laravel\artisan;
use function PHPUnit\Framework\assertEquals;
use Spatie\ModelCleanup\CleanupConfig;
use Spatie\ModelCleanup\Commands\CleanUpModelsCommand;
use Spatie\ModelCleanup\Events\ModelCleanedUpEvent;
use Spatie\ModelCleanup\Exceptions\CleanupFailed;
use Spatie\TestTime\TestTime;
use Tests\Models\TestModel;

beforeEach(function () {
    Event::fake();

    TestModelFactory::new()
        ->startingFrom(now()->addDays(3))
        ->forPreviousDays(10)
        ->create();
});

it('can delete old records that are older than a given number of days', function () {
    useCleanupConfig(function (CleanupConfig $cleanupConfig) {
        $cleanupConfig->olderThanDays(2);
    }, TestModel::class);

    artisan(CleanUpModelsCommand::class)->assertExitCode(0);

    assertModelsExistForDays([
        '2020-01-03',
        '2020-01-02',
        '2020-01-01',
        '2019-12-31',
        '2019-12-30',
    ], TestModel::class);

    assertDeleteQueriesExecuted(1);

    Event::assertDispatched(function (ModelCleanedUpEvent $event) {
        assertEquals(5, $event->numberOfDeletedRecords);

        return true;
    });
});

it('can delete old records that are older than a given date', function () {
    useCleanupConfig(function (CleanupConfig $cleanupConfig) {
        $cleanupConfig->olderThan(now()->subDays(2));
    }, TestModel::class);

    artisan(CleanUpModelsCommand::class)->assertExitCode(0);

    assertModelsExistForDays([
        '2020-01-03',
        '2020-01-02',
        '2020-01-01',
        '2019-12-31',
        '2019-12-30',
    ], TestModel::class);
});

it('can use a scope to filter records to be deleted', function () {
    TestModel::query()
        ->whereDate('created_at', '2019-12-29')
        ->update(['status' => 'inactive']);

    useCleanupConfig(function (CleanupConfig $cleanupConfig) {
        $cleanupConfig
            ->olderThanDays(2)
            ->scope(function (Builder $query) {
                $query->where('status', 'inactive');
            });
    }, TestModel::class);

    artisan(CleanUpModelsCommand::class)->assertExitCode(0);

    assertModelsExistForDays([
        '2020-01-03',
        '2020-01-02',
        '2020-01-01',
        '2019-12-31',
        '2019-12-30',
        '2019-12-28',
        '2019-12-27',
        '2019-12-26',
        '2019-12-25',
    ], TestModel::class);
});

test('using a scope will not delete any records not selected by older than', function () {
    TestModel::query()->update(['status' => 'inactive']);

    useCleanupConfig(function (CleanupConfig $cleanupConfig) {
        $cleanupConfig
            ->olderThanDays(2)
            ->scope(function (Builder $query) {
                $query->where('status', 'inactive');
            });
    }, TestModel::class);

    artisan(CleanUpModelsCommand::class)->assertExitCode(0);

    assertModelsExistForDays([
        '2020-01-03',
        '2020-01-02',
        '2020-01-01',
        '2019-12-31',
        '2019-12-30',
    ], TestModel::class);
});

test('if there is no older than used than the scope can target any record', function () {
    TestModel::query()->whereDate('created_at', '<>', '2020-01-01')->update(['status' => 'inactive']);

    useCleanupConfig(function (CleanupConfig $cleanupConfig) {
        $cleanupConfig
            ->scope(function (Builder $query) {
                $query->where('status', 'inactive');
            });
    }, TestModel::class);

    artisan(CleanUpModelsCommand::class)->assertExitCode(0);

    assertModelsExistForDays([
        '2020-01-01',
    ], TestModel::class);
});

it('can delete old records in a chunked way', function () {
    useCleanupConfig(function (CleanupConfig $cleanupConfig) {
        $cleanupConfig
            ->olderThanDays(2)
            ->chunk(2);
    }, TestModel::class);

    artisan(CleanUpModelsCommand::class)->assertExitCode(0);

    assertModelsExistForDays([
        '2020-01-03',
        '2020-01-02',
        '2020-01-01',
        '2019-12-31',
        '2019-12-30',
    ], TestModel::class);

    assertDeleteQueriesExecuted(3);
});

it('can use custom continue while closure when deleting old records in a chunked way', function () {
    useCleanupConfig(function (CleanupConfig $cleanupConfig) {
        $cleanupConfig
            ->olderThanDays(2)
            ->chunk(2, function (int $numberOfRecordsDeleted) {
                assertEquals(2, $numberOfRecordsDeleted);

                return false;
            });
    }, TestModel::class);

    artisan(CleanUpModelsCommand::class)->assertExitCode(0);

    assertModelsExistForDays([
        '2020-01-03',
        '2020-01-02',
        '2020-01-01',
        '2019-12-31',
        '2019-12-30',
        '2019-12-27',
        '2019-12-26',
        '2019-12-25',
    ], TestModel::class);

    assertDeleteQueriesExecuted(1);
});

it('will stop deleting when no records are being deleted anymore', function () {
    useCleanupConfig(function (CleanupConfig $cleanupConfig) {
        $cleanupConfig
            ->olderThanDays(2)
            ->chunk(2, function () {
                return true;
            });
    }, TestModel::class);

    artisan(CleanUpModelsCommand::class)->assertExitCode(0);

    assertModelsExistForDays([
        '2020-01-03',
        '2020-01-02',
        '2020-01-01',
        '2019-12-31',
        '2019-12-30',
    ], TestModel::class);

    assertDeleteQueriesExecuted(4);

    Event::assertDispatched(function (ModelCleanedUpEvent $event) {
        assertEquals(5, $event->numberOfDeletedRecords);

        return true;
    });
});

it('can use a custom date attribute', function () {
    TestModel::query()->update(['custom_date' => now()]);

    useCleanupConfig(function (CleanupConfig $cleanupConfig) {
        $cleanupConfig
            ->useDateAttribute('custom_date')
            ->olderThanDays(20);
    }, TestModel::class);

    TestTime::addDays(20);
    artisan(CleanUpModelsCommand::class)->assertExitCode(0);
    assertEquals(10, TestModel::count());

    TestTime::addDay();
    artisan(CleanUpModelsCommand::class)->assertExitCode(0);
    assertEquals(0, TestModel::count());
});

it('will not delete all records when nothing has been specified on cleanup config', function () {
    useCleanupConfig(function (CleanupConfig $cleanupConfig) {
    }, TestModel::class);

    assertExceptionThrown(function () {
        artisan(CleanUpModelsCommand::class)->assertExitCode(0);
    }, CleanupFailed::class);

    assertEquals(10, TestModel::count());
});
