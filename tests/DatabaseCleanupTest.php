<?php

namespace Spatie\DatabaseCleanup\Test;

use Spatie\DatabaseCleanup\Test\Models\DummyItem;
use Spatie\DatabaseCleanup\Test\Models\DummyClass;
use Illuminate\Contracts\Console\Kernel;

class DatabaseCleanupTest extends TestCase
{
    /** @test */
    public function it_can_delete_expired_records_from_a_database()
    {
        DummyItem::cleanUpModel(DummyItem::query())->delete();

        $this->assertTrue(DummyItem::count() === 10);
    }

    /** @test */
    public function it_can_cleanup_a_database_running_command_with_models_config_only()
    {
        $this->app['config']->set('laravel-database-cleanup',
            [
                'models' => [DummyItem::class],
                'directories' => [],
            ]);

        $this->app->make(Kernel::class)->call('databaseCleanup:clean');

        $this->assertTrue(DummyItem::count() === 10);
    }

    /** @test */
    public function it_can_cleanup_a_database_running_command_with_directories_config_only()
    {
        $this->setConfig();

        $this->app->make(Kernel::class)->call('databaseCleanup:clean');

        $this->assertTrue(DummyItem::count() === 10);
    }

    /** @test */
    public function it_can_cleanup_a_database_running_command_with_models_and_directories_config()
    {
        $this->app['config']->set('laravel-database-cleanup',
            [
                'models' => [DummyItem::class],
                'directories' => [__DIR__.'/Models']
            ]);

        $this->app->make(Kernel::class)->call('databaseCleanup:clean');

        $this->assertTrue(DummyItem::count() === 10);
    }

    /** @test */
    public function it_does_not_clean_up_models_that_do_not_implement_gets_cleaned_up()
    {
        $this->setConfig();

        $this->app->make(Kernel::class)->call('databaseCleanup:clean');

        $this->assertTrue(DummyClass::count() === 10);
    }

    protected function setConfig()
    {
        $this->app['config']->set('laravel-database-cleanup',
            [
                'models' => [],
                'directories' => [__DIR__ . '/Models']
            ]);
    }
}
