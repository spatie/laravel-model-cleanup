<?php

namespace Tests\Models;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\ModelCleanup\CleanupConfig;
use Spatie\ModelCleanup\GetsCleanedUp;

class TestSoftDeletableModel extends Model implements GetsCleanedUp
{
    use SoftDeletes;

    protected $table = 'test_soft_deletable_models';

    protected $guarded = [];

    protected $casts = [
        'custom_date' => 'timestamp',
    ];

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
