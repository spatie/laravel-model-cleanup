<?php

namespace Spatie\ModelCleanup\Test\TestModels;

use Spatie\ModelCleanup\CleanupConfig\CleanupConfig;
use Spatie\ModelCleanup\CleanupConfig\CleanupConfigFactory;

class ChunkOneCleanupConfigFactory implements CleanupConfigFactory
{
    public static function getCleanupConfig(): CleanupConfig
    {
        return CleanupConfig::new()->chunk(1);
    }
}
