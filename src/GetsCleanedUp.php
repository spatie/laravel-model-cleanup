<?php

namespace Spatie\DatabaseCleanup;

use Illuminate\Database\Eloquent\Builder;

interface GetsCleanedUp
{
    public static function cleanUpModel(Builder $query) : Builder;
}
