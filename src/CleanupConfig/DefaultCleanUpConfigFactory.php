<?php

namespace Spatie\ModelCleanup\CleanupConfig;

class DefaultCleanUpConfigFactory implements CleanupConfigFactory
{
    public static function getCleanupConfig(): CleanupConfig
    {
        return CleanupConfig::new();
    }
}
