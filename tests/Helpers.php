<?php

namespace Tests;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Models\TestModel;

function useCleanupConfig(Closure $closure)
{
    TestModel::setCleanupConfigClosure($closure);

    config()->set('model-cleanup.models', [
        TestModel::class,
    ]);
}

function assertDeleteQueriesExecuted(int $expectedCount)
{
    $actualCount = collect(DB::getQueryLog())
        ->map(function (array $queryProperties) {
            return $queryProperties['query'];
        })
        ->filter(function (string $query) {
            return Str::startsWith($query, 'delete');
        })
        ->count();

    assertEquals(
        $expectedCount,
        $actualCount,
        "Expected {$expectedCount} delete queries, but {$actualCount} delete queries where executed."
    );
}
