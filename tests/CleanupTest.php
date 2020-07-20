<?php

namespace Spatie\ModelCleanup\Test;

use Closure;
use Spatie\ModelCleanup\CleanupConfig;
use Spatie\ModelCleanup\CleanUpModelsCommand;
use Spatie\ModelCleanup\Test\Models\TestModel;

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

    protected function useCleanupConfig(Closure $closure)
    {
        TestModel::setCleanupConfigClosure($closure);

        config()->set('model-cleanup.models', [
            TestModel::class,
        ]);
    }
}
