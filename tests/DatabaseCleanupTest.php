<?php

namespace Spatie\ModelCleanup\Test;

use Spatie\ModelCleanup\ModelWasCleanedUp;
use Spatie\ModelCleanup\Test\Models\CleanableItem;
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

        $this->app['config']->set('laravel-model-cleanup',
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

    /** @test */
    public function it_will_fire_off_an_event_when_a_model_has_been_cleaned()
    {
        $this->assertCount(20, CleanableItem::all());

        $this->app['config']->set('laravel-model-cleanup',
            [
                'models' => [CleanableItem::class],
                'directories' => [],

            ]);

        $this->app->make(Kernel::class)->call('clean:models');

        $firedEvent = $this->getFiredEvent(ModelWasCleanedUp::class);

        $this->assertInstanceOf(ModelWasCleanedUp::class, $firedEvent);

        $this->assertSame(10, $firedEvent->numberOfDeletedRecords);
    }

    /** @test */
    public function it_will_fire_off_an_event_when_a_model_has_been_cleaned_even_if_no_records_were_deleted()
    {
        CleanableItem::truncate();

        $this->app['config']->set('laravel-model-cleanup',
            [
                'models' => [CleanableItem::class],
                'directories' => [],

            ]);

        $this->app->make(Kernel::class)->call('clean:models');

        $firedEvent = $this->getFiredEvent(ModelWasCleanedUp::class);

        $this->assertInstanceOf(ModelWasCleanedUp::class, $firedEvent);

        $this->assertSame(0, $firedEvent->numberOfDeletedRecords);
    }

    protected function setConfigThatCleansUpDirectory()
    {
        $this->app['config']->set('laravel-model-cleanup',
            [
                'models' => [],
                'directories' => [__DIR__.'/Models'],
            ]);
    }
}
