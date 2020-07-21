<?php

namespace Spatie\ModelCleanup;

use Spatie\ModelCleanup\CleanupConfig\CleanupConfig;

/** @mixin \Illuminate\Database\Eloquent\Model */
interface GetsCleanedUp
{
    public function cleanUp(CleanupConfig $config): void;
}
