<?php

namespace Spatie\DatabaseCleanup\Test\Models;

use Illuminate\Database\Eloquent\Model;

class DummyClass extends Model
{
    protected $table = 'dummy_class';
    protected $guarded = [];
    public $timestamps = false;

}
