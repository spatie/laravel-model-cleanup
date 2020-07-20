<?php

namespace Spatie\ModelCleanup\Test;

use Illuminate\Contracts\Console\Kernel;
use Spatie\ModelCleanup\Test\Models\ForceCleanableItem;

class DatabaseForceCleanupTest extends TestCase
{
    /** @test */
    public function it_can_delete_expired_records_from_a_database()
    {
        $this->assertEquals(20, ForceCleanableItem::withTrashed()->count());

        ForceCleanableItem::forceCleanUp(ForceCleanableItem::query())->forceDelete();

        $this->assertEquals(10, ForceCleanableItem::withTrashed()->count());
    }

    /** @test */
    public function it_can_cleanup_the_models_specified_in_the_config_file()
    {
        $this->assertEquals(20, ForceCleanableItem::withTrashed()->count());

        $this->app['config']->set(
            'model-cleanup',
            [
                'directories' => [],
                'models' => [ForceCleanableItem::class],
            ]
        );

        $this->app->make(Kernel::class)->call('clean:models');

        $this->assertEquals(10, ForceCleanableItem::withTrashed()->count());
    }

    /** @test */
    public function it_can_cleanup_the_directories_specified_in_the_config_file()
    {
        $this->assertEquals(20, ForceCleanableItem::withTrashed()->count());

        $this->setConfigThatCleansUpDirectory();

        $this->app->make(Kernel::class)->call('clean:models');

        $this->assertEquals(10, ForceCleanableItem::withTrashed()->count());
    }

    protected function setConfigThatCleansUpDirectory()
    {
        $this->app['config']->set(
            'model-cleanup',
            [
                'directories' => [__DIR__.'/Models'],
                'models' => [],
            ]
        );
    }
}
