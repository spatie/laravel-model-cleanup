<?php

return [

    /*
     * All models in this array that implement `Spatie\ModelCleanupGetsCleanedUp`
     * will be cleaned.
     */
    'models' => [
        // App\Models\YourModel::class,
    ],

    /*
     * Here you can specify the class that will return the configuration on how
     * models should be cleaned up by default.
     */
    'default_cleanup_config' => Spatie\ModelCleanup\DefaultCleanUpConfigFactory::class,
];
