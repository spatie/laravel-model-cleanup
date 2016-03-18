<?php

namespace Spatie\ModelCleanup\Test\Models;

use Illuminate\Database\Eloquent\Model;

class UncleanableItem extends Model
{
    protected $table = 'uncleanable_items';

    protected $guarded = [];

    public $timestamps = false;
}
