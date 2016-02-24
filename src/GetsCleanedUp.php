<?php


namespace Spatie\DatabaseCleanup;

use Illuminate\Database\Query\Builder;

interface GetsCleanedUp
{
    public static function cleanUpModels(Builder $query) : Builder;

}