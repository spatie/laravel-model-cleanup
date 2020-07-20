<?php

namespace Spatie\ModelCleanup\Test;

use Illuminate\Contracts\Console\Kernel;
use Spatie\ModelCleanup\Test\Models\TestModel;

class DatabaseCleanupTest extends TestCase
{
    /** @test */
    public function it_can_delete_expired_records_from_a_database()
    {
        $this->assertCount(20, TestModel::all());

        TestModel::cleanUp(TestModel::query())->delete();

        $this->assertCount(10, TestModel::all());
    }

    /** @test */
    public function it_can_cleanup_the_models_specified_in_the_config_file()
    {
        $this->assertCount(20, TestModel::all());

        $this->app['config']->set(
            'model-cleanup',
            [
                'directories' => [],
                'models' => [TestModel::class],
            ]
        );

        $this->app->make(Kernel::class)->call('clean:models');

        $this->assertCount(10, TestModel::all());
    }

    /** @test */
    public function it_can_cleanup_the_directories_specified_in_the_config_file()
    {
        $this->assertCount(20, TestModel::all());

        $this->setConfigThatCleansUpDirectory();

        $this->app->make(Kernel::class)->call('clean:models');

        $this->assertCount(10, TestModel::all());
    }
}
