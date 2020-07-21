<?php

namespace Spatie\ModelCleanup\Test;

use Illuminate\Database\Eloquent\Builder;
use Spatie\ModelCleanup\CleanupConfig\CleanupConfig;
use Spatie\ModelCleanup\CleanUpModelsCommand;
use Spatie\ModelCleanup\Test\Models\TestModel;
use Spatie\ModelCleanup\Test\TestModels\ChunkOneCleanupConfigFactory;

class CleanupTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        TestModelFactory::new()
            ->startingFrom(now()->addDays(3))
            ->forPreviousDays(10)
            ->create();
    }

    /** @test */
    public function it_can_delete_old_records_that_are_older_than_a_given_number_of_days()
    {
        $this->useCleanupConfig(function (CleanupConfig $cleanupConfig) {
            $cleanupConfig->olderThanDays(2);
        });

        $this->artisan(CleanUpModelsCommand::class)->assertExitCode(0);

        $this->assertModelsExistForDays([
            '2020-01-03',
            '2020-01-02',
            '2020-01-01',
            '2019-12-31',
            '2019-12-30',
        ]);

        $this->assertDeleteQueriesExecuted(1);
    }

    /** @test */
    public function it_can_delete_old_records_that_are_older_than_a_given_date()
    {
        $this->useCleanupConfig(function (CleanupConfig $cleanupConfig) {
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

        $this->useCleanupConfig(function (CleanupConfig $cleanupConfig) {
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

        $this->useCleanupConfig(function (CleanupConfig $cleanupConfig) {
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

        $this->useCleanupConfig(function (CleanupConfig $cleanupConfig) {
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
        $this->useCleanupConfig(function (CleanupConfig $cleanupConfig) {
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

        $this->assertDeleteQueriesExecuted(3);
    }

    /** @test */
    public function it_can_use_custom_continue_while_closure_when_deleting_old_records_in_a_chunked_way()
    {
        $this->useCleanupConfig(function (CleanupConfig $cleanupConfig) {
            $cleanupConfig
                ->olderThanDays(2)
                ->chunk(2, function (int $numberOfRecordsDeleted) {
                    $this->assertEquals(2, $numberOfRecordsDeleted);

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

        $this->assertDeleteQueriesExecuted(1);
    }

    /** @test */
    public function it_will_stop_deleting_when_no_records_are_being_deleted_anymore()
    {
        $this->useCleanupConfig(function (CleanupConfig $cleanupConfig) {
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

        $this->assertDeleteQueriesExecuted(4);
    }

    /** @test */
    public function it_will_use_the_default_clean_up_config_from_the_config_file()
    {
        config()->set('model-cleanup.default_cleanup_config', ChunkOneCleanupConfigFactory::class);

        $this->useCleanupConfig(function (CleanupConfig $cleanupConfig) {
            $cleanupConfig->olderThanDays(2);
        });

        $this->artisan(CleanUpModelsCommand::class)->assertExitCode(0);

        $this->assertModelsExistForDays([
            '2020-01-03',
            '2020-01-02',
            '2020-01-01',
            '2019-12-31',
            '2019-12-30',
        ]);

        $this->assertDeleteQueriesExecuted(6);
    }
}
