<?php

namespace Spatie\DatabaseCleanup\Test\Models2;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\DatabaseCleanup\GetsCleanedUp;
use Carbon\Carbon;

class DummyItem extends Model implements GetsCleanedUp
{
    protected $table = 'dummy_items';
    protected $guarded = [];
    public $timestamps = false;

    public static function cleanUpModel(Builder $query) : Builder
    {
        return $query->where('created_at', '<', Carbon::now()->subDays(365));
    }
}
