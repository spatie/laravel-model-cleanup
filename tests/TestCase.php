<?php

namespace Spatie\ModelCleanup\Test;

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\ModelCleanup\ModelCleanupServiceProvider;
use Spatie\ModelCleanup\Test\Models\TestModel;
use Spatie\TestTime\TestTime;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);

        TestTime::freeze('Y-m-d H:i:s', '2020-01-01 00:00:00');
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
            $table->timestamp('updated_at');
        });
    }

    protected function assertModelsExistForDays(array $expectedDates)
    {
        $actualDates = TestModel::all()
            ->pluck('created_at')
            ->map(fn (Carbon $createdAt) => $createdAt->format('Y-m-d'))
            ->toArray();

        $this->assertEquals($expectedDates, $actualDates);
    }
}
