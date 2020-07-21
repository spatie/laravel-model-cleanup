<?php


namespace Spatie\ModelCleanup\CleanupConfig;

interface CleanupConfigFactory
{
    public static function getCleanupConfig(): CleanupConfig;
}
