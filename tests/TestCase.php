<?php

namespace Spatie\DatabaseCleanup\Test;

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Spatie\DatabaseCleanup\DatabaseCleanupServiceProvider;
use Spatie\DatabaseCleanup\Test\Models\DummyItem;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->setUpDatabase($this->app);
    }
    protected function getPackageProviders($app)
    {
        return [DatabaseCleanupServiceProvider::class];
    }
    public function  getEnvironmentSetUp($app)
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
        $app['db']->connection()->getSchemaBuilder()->create('dummy_items', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('created_at');
        });

        $this->createDummyItems();


    }
    public function getTempDirectory($suffix = '')
    {
        return __DIR__.'/temp'.($suffix == '' ? '' : '/'.$suffix);
    }

    protected function createDummyItems()
    {
        foreach (range(1,10) as $index){
            DummyItem::create([
                'created_at'     =>  Carbon::now()->subYear(1)->subDays(7)
            ]);
        }

    }
}