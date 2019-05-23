<?php

namespace Spatie\ModelCleanup\Test;

use Spatie\ModelCleanup\ModelWasCleanedUp;
use Spatie\ModelCleanup\Test\Models\CleanableItem;
use Spatie\ModelCleanup\Test\Models\ModelsInSubDirectory\SubDirectoryCleanableItem;
use Spatie\ModelCleanup\Test\Models\ModelsInSubDirectory\SubDirectoryUncleanableItem;
use Spatie\ModelCleanup\Test\Models\UncleanableItem;
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

        $this->app['config']->set('model-cleanup',
            [
                'directories' => [],
                'models' => [CleanableItem::class],
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

    /** @test */
    public function it_can_cleanup_the_sub_directories_of_the_directories_specified_in_the_config_file()
    {
        $this->assertCount(20, SubDirectoryCleanableItem::all());

        $this->setConfigThatCleansUpDirectory();

        $this->app->make(Kernel::class)->call('clean:models');

        $this->assertCount(10, SubDirectoryCleanableItem::all());
    }

    /** @test */
    public function it_leaves_models_without_the_GetCleanUp_trait_untouched_in_sub_directory()
    {
        $this->assertCount(10, SubDirectoryUncleanableItem::all());

        $this->setConfigThatCleansUpDirectory();

        $this->app->make(Kernel::class)->call('clean:models');

        $this->assertCount(10, SubDirectoryUncleanableItem::all());
    }

    protected function setConfigThatCleansUpDirectory()
    {
        $this->app['config']->set('model-cleanup',
            [
                'directories' => [__DIR__.'/Models'],
                'models' => [],
            ]);
    }
}
