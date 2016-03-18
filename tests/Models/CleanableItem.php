<?php

namespace Spatie\ModelCleanup\Test\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\ModelCleanup\GetsCleanedUp;
use Carbon\Carbon;

class CleanableItem extends Model implements GetsCleanedUp
{
    protected $table = 'cleanable_items';

    protected $guarded = [];

    public $timestamps = false;

    public static function cleanUp(Builder $query) : Builder
    {
        return $query->where('created_at', '<', Carbon::now()->subYear());
    }
}
