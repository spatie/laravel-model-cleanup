<?php

namespace Spatie\ModelCleanup\Exceptions;

use Exception;

class CleanupFailed extends Exception
{
    public static function create(array $exceptions): self
    {
        $listOfExceptions = collect($exceptions)
            ->map(function ($exception) {
                $modelClass = get_class($exception['model']);

                $exceptionClass = get_class($exception['exception']);

                $exceptionMessage = $exception['exception']->getMessage();

                return "Model `$modelClass`: {$exceptionClass} - {$exceptionMessage}";
            });

        return new static("Failed to clean up some models. {$listOfExceptions}");
    }
}
