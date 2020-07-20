<?php

namespace Spatie\ModelCleanup\Test;

use Carbon\Carbon;
use Event;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\ModelCleanup\ModelCleanupServiceProvider;
use Spatie\ModelCleanup\Test\Models\TestModel;
use Spatie\ModelCleanup\Test\Models\ForceCleanableItem;
use Spatie\ModelCleanup\Test\Models\ModelsInSubDirectory\SubDirectoryCleanableItem;
use Spatie\ModelCleanup\Test\Models\ModelsInSubDirectory\SubDirectoryUncleanableItem;
use Spatie\ModelCleanup\Test\Models\UncleanableItem;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
    }

    protected function getPackageProviders($app)
    {
        return [ModelCleanupServiceProvider::class];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        config()->set('app.key', '6rE9Nz59bGRbeMATftriyQjrpF7DcOQm');
    }

    protected function setUpDatabase($app)
    {
      Schema::create('test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('created_at');
        });
    }
}
