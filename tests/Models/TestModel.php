<?php

namespace Spatie\ModelCleanup\Test\Models;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Spatie\ModelCleanup\CleanupConfig;
use Spatie\ModelCleanup\GetsCleanedUp;

class TestModel extends Model implements GetsCleanedUp
{
    protected $table = 'test_models';

    protected $guarded = [];

    protected static $cleanupClosure;

    public static function setCleanupConfigClosure(Closure $cleanupClosure)
    {
        static::$cleanupClosure = $cleanupClosure;
    }

    public function cleanUp(CleanupConfig $config): void
    {
        (static::$cleanupClosure)($config);
    }
}
