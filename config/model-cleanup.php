<?php

use Spatie\ModelCleanup\CleanupConfig;

return [

    /*
     * All models in this array that use the GetsCleanedUp interface will be cleaned.
     */
    'models' => [
        // App\LogItem::class,
    ],

    'default_cleanup_config' => CleanupConfig::new(),
];
