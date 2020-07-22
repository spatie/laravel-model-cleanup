<?php

namespace Tests;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Event;
use function Pest\Laravel\artisan;
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
    });

    artisan(CleanUpModelsCommand::class)->assertExitCode(0);

    $this->assertModelsExistForDays([
        '2020-01-03',
        '2020-01-02',
        '2020-01-01',
        '2019-12-31',
        '2019-12-30',
    ]);

    assertDeleteQueriesExecuted(1);

    Event::assertDispatched(function (ModelCleanedUpEvent $event) {
        assertEquals(5, $event->numberOfDeletedRecords);

        return true;
    });
});


class CleanupTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Event::fake();

        TestModelFactory::new()
            ->startingFrom(now()->addDays(3))
            ->forPreviousDays(10)
            ->create();
    }

    /** @test */
    public function it_can_delete_old_records_that_are_older_than_a_given_date()
    {
        useCleanupConfig(function (CleanupConfig $cleanupConfig) {
            $cleanupConfig->olderThan(now()->subDays(2));
        });

        $this->artisan(CleanUpModelsCommand::class)->assertExitCode(0);

        $this->assertModelsExistForDays([
            '2020-01-03',
            '2020-01-02',
            '2020-01-01',
            '2019-12-31',
            '2019-12-30',
        ]);
    }

    /** @test */
    public function it_can_use_a_scope_to_filter_records_to_be_deleted()
    {
        TestModel::query()
            ->whereDate('created_at', '2019-12-29')
            ->update(['status' => 'inactive']);

        useCleanupConfig(function (CleanupConfig $cleanupConfig) {
            $cleanupConfig
                ->olderThanDays(2)
                ->scope(function (Builder $query) {
                    $query->where('status', 'inactive');
                });
        });

        $this->artisan(CleanUpModelsCommand::class)->assertExitCode(0);

        $this->assertModelsExistForDays([
            '2020-01-03',
            '2020-01-02',
            '2020-01-01',
            '2019-12-31',
            '2019-12-30',
            '2019-12-28',
            '2019-12-27',
            '2019-12-26',
            '2019-12-25',
        ]);
    }

    /** @test */
    public function using_a_scope_will_not_delete_any_records_not_selected_by_older_than()
    {
        TestModel::query()->update(['status' => 'inactive']);

        useCleanupConfig(function (CleanupConfig $cleanupConfig) {
            $cleanupConfig
                ->olderThanDays(2)
                ->scope(function (Builder $query) {
                    $query->where('status', 'inactive');
                });
        });

        $this->artisan(CleanUpModelsCommand::class)->assertExitCode(0);

        $this->assertModelsExistForDays([
            '2020-01-03',
            '2020-01-02',
            '2020-01-01',
            '2019-12-31',
            '2019-12-30',
        ]);
    }

    /** @test */
    public function if_there_is_no_older_than_used_than_the_scope_can_target_any_record()
    {
        TestModel::query()->whereDate('created_at', '<>', '2020-01-01')->update(['status' => 'inactive']);

        useCleanupConfig(function (CleanupConfig $cleanupConfig) {
            $cleanupConfig
                ->scope(function (Builder $query) {
                    $query->where('status', 'inactive');
                });
        });

        $this->artisan(CleanUpModelsCommand::class)->assertExitCode(0);

        $this->assertModelsExistForDays([
            '2020-01-01',
        ]);
    }

    /** @test */
    public function it_can_delete_old_records_in_a_chunked_way()
    {
        useCleanupConfig(function (CleanupConfig $cleanupConfig) {
            $cleanupConfig
                ->olderThanDays(2)
                ->chunk(2);
        });

        $this->artisan(CleanUpModelsCommand::class)->assertExitCode(0);

        $this->assertModelsExistForDays([
            '2020-01-03',
            '2020-01-02',
            '2020-01-01',
            '2019-12-31',
            '2019-12-30',
        ]);

        assertDeleteQueriesExecuted(3);
    }

    /** @test */
    public function it_can_use_custom_continue_while_closure_when_deleting_old_records_in_a_chunked_way()
    {
        useCleanupConfig(function (CleanupConfig $cleanupConfig) {
            $cleanupConfig
                ->olderThanDays(2)
                ->chunk(2, function (int $numberOfRecordsDeleted) {
                    assertEquals(2, $numberOfRecordsDeleted);

                    return false;
                });
        });

        $this->artisan(CleanUpModelsCommand::class)->assertExitCode(0);

        $this->assertModelsExistForDays([
            '2020-01-03',
            '2020-01-02',
            '2020-01-01',
            '2019-12-31',
            '2019-12-30',
            '2019-12-27',
            '2019-12-26',
            '2019-12-25',
        ]);

        assertDeleteQueriesExecuted(1);
    }

    /** @test */
    public function it_will_stop_deleting_when_no_records_are_being_deleted_anymore()
    {
        useCleanupConfig(function (CleanupConfig $cleanupConfig) {
            $cleanupConfig
                ->olderThanDays(2)
                ->chunk(2, function () {
                    return true;
                });
        });

        $this->artisan(CleanUpModelsCommand::class)->assertExitCode(0);

        $this->assertModelsExistForDays([
            '2020-01-03',
            '2020-01-02',
            '2020-01-01',
            '2019-12-31',
            '2019-12-30',
        ]);

        assertDeleteQueriesExecuted(4);

        Event::assertDispatched(function (ModelCleanedUpEvent $event) {
            assertEquals(5, $event->numberOfDeletedRecords);

            return true;
        });
    }

    /** @test */
    public function it_can_use_a_custom_date_attribute()
    {
        TestModel::query()->update(['custom_date' => now()]);

        useCleanupConfig(function (CleanupConfig $cleanupConfig) {
            $cleanupConfig
                ->useDateAttribute('custom_date')
                ->olderThanDays(20);
        });

        TestTime::addDays(20);
        $this->artisan(CleanUpModelsCommand::class)->assertExitCode(0);
        assertEquals(10, TestModel::count());

        TestTime::addDay();
        $this->artisan(CleanUpModelsCommand::class)->assertExitCode(0);
        assertEquals(0, TestModel::count());
    }

    /** @test */
    public function it_will_not_delete_all_records_when_nothing_has_been_specified_on_cleanup_config()
    {
        useCleanupConfig(function (CleanupConfig $cleanupConfig) {
        });

        $this->assertExceptionThrown(function () {
            $this->artisan(CleanUpModelsCommand::class)->assertExitCode(0);
        }, CleanupFailed::class);

        assertEquals(10, TestModel::count());
    }
}
