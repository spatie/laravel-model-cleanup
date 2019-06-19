<?php

namespace Spatie\ModelCleanup\Test\Models\ModelsInSubDirectory;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\ModelCleanup\GetsCleanedUp;
use Carbon\Carbon;

class SubDirectoryCleanableItem extends Model implements GetsCleanedUp
{
    protected $table = 'sub_dir_cleanable_items';

    protected $guarded = [];

    public $timestamps = false;

    public static function cleanUp(Builder $query) : Builder
    {
        return $query->where('created_at', '<', Carbon::now()->subYear());
    }
}
