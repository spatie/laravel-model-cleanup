<?php

namespace Spatie\ModelCleanup;

use Illuminate\Database\Eloquent\Builder;

interface GetsForcedCleanedUp
{
    /**
     * Returns a query that determines which models will get completly cleaned up. On
     * cleanup, the `forceDelete` method will be appended to the query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function forceCleanUp(Builder $query) : Builder;
}
