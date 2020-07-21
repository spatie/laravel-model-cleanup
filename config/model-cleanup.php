<?php

use Spatie\ModelCleanup\CleanupConfig;

return [

    /*
     * All models in this array that implement `Spatie\ModelCleanupGetsCleanedUp`
     * will be cleaned.
     */
    'models' => [
        // App\Models\LogItem::class,
    ],

    /*
     * Here you can specify how models should be cleaned by default.
     */
    'default_cleanup_config' => CleanupConfig::new(),
];
