<?php

namespace Spatie\DatabaseCleanup\Test;

use Spatie\DatabaseCleanup\Test\Models\CleanableItem;
use Spatie\DatabaseCleanup\Test\Models\UncleanableItem;
use Illuminate\Contracts\Console\Kernel;

class DatabaseCleanupTest extends TestCase
{
    /** @test */
    public function it_can_delete_expired_records_from_a_database()
    {
        $this->assertCount(20, CleanableItem::all());

        CleanableItem::cleanUp(CleanableItem::query())->delete();

        $this->assertCount(10, CleanableItem::all());
    }

    /** @test */
    public function it_can_cleanup_the_models_specified_in_the_config_file()
    {
        $this->assertCount(20, CleanableItem::all());

        $this->app['config']->set('laravel-database-cleanup',
            [
                'models' => [CleanableItem::class],
                'directories' => [],
            ]);

        $this->app->make(Kernel::class)->call('clean:models');

        $this->assertCount(10, CleanableItem::all());
    }

    /** @test */
    public function it_can_cleanup_the_directories_specified_in_the_config_file()
    {
        $this->assertCount(20, CleanableItem::all());

        $this->setConfigThatCleansUpDirectory();

        $this->app->make(Kernel::class)->call('clean:models');

        $this->assertCount(10, CleanableItem::all());
    }

    /** @test */
    public function it_leaves_models_without_the_GetCleanUp_trait_untouched()
    {
        $this->assertCount(10, UncleanableItem::all());

        $this->setConfigThatCleansUpDirectory();

        $this->app->make(Kernel::class)->call('clean:models');

        $this->assertCount(10, UncleanableItem::all());
    }

    protected function setConfigThatCleansUpDirectory()
    {
        $this->app['config']->set('laravel-database-cleanup',
            [
                'models' => [],
                'directories' => [__DIR__.'/Models'],
            ]);
    }
}
