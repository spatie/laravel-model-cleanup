<?php

namespace Spatie\DatabaseCleanup\Test\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\DatabaseCleanup\GetsCleanedUp;
use Carbon\Carbon;

class DummyItem extends Model implements GetsCleanedUp
{
    protected $table = 'dummy_items';
    protected $fillable = ['created_at'];
    protected $dates = ['created_at'];
    public $timestamps = false;


    public static function cleanUpModels(Builder $query) : Builder
    {
        return $query->where('created_at', '<', Carbon::now()->subDays(365));
    }

}