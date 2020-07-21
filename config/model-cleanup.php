<?php

use Spatie\ModelCleanup\CleanupConfig\CleanupConfig;
use Spatie\ModelCleanup\CleanupConfig\DefaultCleanUpConfigFactory;

return [

    /*
     * All models in this array that implement `Spatie\ModelCleanupGetsCleanedUp`
     * will be cleaned.
     */
    'models' => [
        // App\Models\LogItem::class,
    ],

    /*
     * Here you can specify the class that will return the configuration on how
     * models should be cleaned up by default.
     */
    'default_cleanup_config' => DefaultCleanUpConfigFactory::class,
];
