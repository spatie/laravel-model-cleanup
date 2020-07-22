<?php

namespace Spatie\ModelCleanup;

/** @mixin \Illuminate\Database\Eloquent\Model */
interface GetsCleanedUp
{
    public function cleanUp(CleanupConfig $config): void;
}
