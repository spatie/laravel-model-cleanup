<?php

namespace Spatie\DatabaseCleanup\Test;

use Spatie\DatabaseCleanup\Test\Models\DummyItem;

class DatabaseCleanupTest extends TestCase
{
    /** @test */
    public function it_can_delete_expired_records_from_a_database()
    {
        DummyItem::cleanUpModels(DummyItem::query())->delete();
        $this->assertFalse(DummyItem::count() > 0);
    }

    /** @test */
    public function it_can_cleanup_a_database_via_an_command_with_models_config()
    {
        $this->app['config']->set('laravel-database-cleanup',
            ['models' => [DummyItem::class],
            'directories' => []]);

        $this->app->make(\Illuminate\Contracts\Console\Kernel::class)->call('db:deleteExpiredRecords');

        $this->assertFalse(DummyItem::count() > 0);
    }

    /** @test */
    public function it_can_cleanup_a_database_via_an_command_with_directories_config()
    {
        $this->app['config']->set('laravel-database-cleanup',
            ['models' => [], 'directories' => ['models' => __DIR__.'/Models']]);

        $this->app->make(\Illuminate\Contracts\Console\Kernel::class)->call('db:deleteExpiredRecords');

        $this->assertFalse(DummyItem::count() > 0);
    }
}
