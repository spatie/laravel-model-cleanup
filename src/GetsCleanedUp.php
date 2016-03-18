<?php

namespace Spatie\ModelCleanup;

use Illuminate\Database\Eloquent\Builder;

interface GetsCleanedUp
{
    /**
     * Returns a query that determines which models will get cleaned up. On
     * cleanup, the `delete` method will be appended to the query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function cleanUp(Builder $query) : Builder;
}
