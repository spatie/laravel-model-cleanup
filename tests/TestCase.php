<?php

namespace Spatie\ModelCleanup\Test;

use Carbon\Carbon;
use Event;
use Illuminate\Database\Schema\Blueprint;
use Spatie\ModelCleanup\ModelCleanupServiceProvider;
use Spatie\ModelCleanup\Test\Models\CleanableItem;
use Spatie\ModelCleanup\Test\Models\ForceCleanableItem;
use Spatie\ModelCleanup\Test\Models\ModelsInSubDirectory\SubDirectoryCleanableItem;
use Spatie\ModelCleanup\Test\Models\ModelsInSubDirectory\SubDirectoryUncleanableItem;
use Spatie\ModelCleanup\Test\Models\UncleanableItem;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    /** @var array */
    protected $firedEvents = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);

        Event::listen('*', function ($event) {
            $this->firedEvents[] = $event;
        });
    }

    protected function getPackageProviders($app)
    {
        return [ModelCleanupServiceProvider::class];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => $this->getTempDirectory().'/database.sqlite',
            'prefix' => '',
        ]);
        $app['config']->set('app.key', '6rE9Nz59bGRbeMATftriyQjrpF7DcOQm');
    }

    protected function setUpDatabase($app)
    {
        file_put_contents($this->getTempDirectory().'/database.sqlite', null);

        $app['db']->connection()->getSchemaBuilder()->create('cleanable_items', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('created_at');
        });

        $app['db']->connection()->getSchemaBuilder()->create('forced_cleanable_items', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
            $table->timestamp('deleted_at');
        });

        $app['db']->connection()->getSchemaBuilder()->create('sub_dir_cleanable_items', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('created_at');
        });

        $app['db']->connection()->getSchemaBuilder()->create('uncleanable_items', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('created_at');
        });

        $app['db']->connection()->getSchemaBuilder()->create('sub_dir_uncleanable_items', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('created_at');
        });

        $this->createDatabaseRecords();
    }

    public function getTempDirectory($suffix = '')
    {
        return __DIR__.'/temp'.($suffix == '' ? '' : '/'.$suffix);
    }

    protected function createDatabaseRecords()
    {
        foreach (range(1, 10) as $index) {
            CleanableItem::create([
                'created_at' => Carbon::now()->subYear(1)->subDays(7),
            ]);

            CleanableItem::create([
                'created_at' => Carbon::now()->subMonth(),
            ]);

            ForceCleanableItem::create([
                'created_at' => Carbon::now()->subMonth(),
                'deleted_at' => Carbon::now()->subDays(2),
            ]);

            SubDirectoryCleanableItem::create([
                'created_at' => Carbon::now()->subYear(1)->subDays(7),
            ]);

            SubDirectoryCleanableItem::create([
                'created_at' => Carbon::now()->subMonth(),
            ]);

            ForceCleanableItem::create([
                'created_at' => Carbon::now()->subMonth(),
                'deleted_at' => Carbon::now(),
            ]);

            UncleanableItem::create([
                'created_at' => Carbon::now()->subYear(1)->subDays(7),
            ]);

            SubDirectoryUncleanableItem::create([
                'created_at' => Carbon::now()->subYear(1)->subDays(7),
            ]);
        }
    }

    public function getFiredEvent($eventClassName)
    {
        return collect($this->firedEvents)
            ->filter(function ($event) use ($eventClassName) {
                if ($event instanceof $eventClassName) {
                    return true;
                };

                return $event == $eventClassName;
            })
            ->first();
    }
}
