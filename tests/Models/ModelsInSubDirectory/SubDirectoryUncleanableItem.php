<?php

namespace Spatie\ModelCleanup\Test\Models\ModelsInSubDirectory;

use Illuminate\Database\Eloquent\Model;

class SubDirectoryUncleanableItem extends Model
{
    protected $table = 'sub_dir_uncleanable_items';

    protected $guarded = [];

    public $timestamps = false;
}
