<?php

namespace Spatie\ModelCleanup;

interface GetsCleanedUp
{
    public function cleanUp(CleanupConfig $config): void;
}
