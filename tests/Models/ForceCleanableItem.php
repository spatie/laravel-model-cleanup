<?php

namespace Spatie\ModelCleanup\Test\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\ModelCleanup\GetsForcedCleanedUp;

class ForceCleanableItem extends Model implements GetsForcedCleanedUp
{
    use SoftDeletes;

    protected $table = 'forced_cleanable_items';
    protected $guarded = [];

    public static function forceCleanUp(Builder $query) : Builder
    {
        return $query->onlyTrashed()->where('deleted_at', '<', Carbon::now()->subDay());
    }
}
