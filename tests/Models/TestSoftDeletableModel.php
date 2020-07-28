<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class TestSoftDeletableModel extends TestModel
{
    use SoftDeletes;

    protected $table = 'test_soft_deletable_models';
}
