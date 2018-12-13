<?php

namespace Spatie\ModelCleanup\Test\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\ModelCleanup\GetsForcedCleanedUp;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

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
