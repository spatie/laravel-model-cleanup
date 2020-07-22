<?php

namespace Tests;

use Carbon\Carbon;
use Closure;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\ModelCleanup\ModelCleanupServiceProvider;
use Tests\Models\TestModel;
use Spatie\TestTime\TestTime;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);

        TestTime::freeze('Y-m-d H:i:s', '2020-01-01 00:00:00');

        DB::enableQueryLog();
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
            $table->timestamp('custom_date')->nullable();
            $table->string('status')->default('active');
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

    protected function assertExceptionThrown(
        callable $callable,
        string $expectedExceptionClass = Exception::class
    ): void {
        try {
            $callable();

            $this->assertTrue(false, "Expected exception `{$expectedExceptionClass}` was not thrown.");
        } catch (Exception $exception) {
            $actualExceptionClass = get_class($exception);

            $this->assertInstanceOf($expectedExceptionClass, $exception, "Unexpected exception `$actualExceptionClass` thrown. Expected exception `$expectedExceptionClass`");
        }
    }
}
