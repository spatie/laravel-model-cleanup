<?php

namespace Spatie\ModelCleanup;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

class CleanUpModelsCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'clean:models';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up models.';

    protected $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
    }

    public function handle()
    {
        $this->comment('Cleaning models...');

        // Cleaning Normal models
        $cleanableModels = $this->getModelsThatShouldBeCleanedUp();
        $this->cleanUp($cleanableModels);

        // Cleaning softdeletes models
        $cleanableModels = $this->getModelsThatShouldBeForcedCleanedUp();
        $this->forceCleanUp($cleanableModels);

        $this->comment('All done!');
    }

    protected function getModelsThatShouldBeCleanedUp() : Collection
    {
        $directories = config('model-cleanup.directories');

        $modelsFromDirectories = $this->getAllModelsFromEachDirectory($directories);

        return $modelsFromDirectories
            ->merge(collect(config('model-cleanup.models')))
            ->filter(function ($modelClass) {
                return in_array(GetsCleanedUp::class, class_implements($modelClass));
            });
    }

    protected function getModelsThatShouldBeForcedCleanedUp() : Collection
    {
        $directories = config('model-cleanup.directories');

        $modelsFromDirectories = $this->getAllModelsFromEachDirectory($directories);

        return $modelsFromDirectories
            ->merge(collect(config('model-cleanup.models')))
            ->filter(function ($modelClass) {
                return in_array(GetsForcedCleanedUp::class, class_implements($modelClass));
            });
    }

    protected function cleanUp(Collection $cleanableModels)
    {
        $cleanableModels->each(function (string $modelClass) {
            $numberOfDeletedRecords = $modelClass::cleanUp($modelClass::query())->delete();

            event(new ModelWasCleanedUp($modelClass, $numberOfDeletedRecords));

            $this->info("Deleted {$numberOfDeletedRecords} record(s) from {$modelClass}.");
        });
    }

    protected function forceCleanUp(Collection $cleanableModels)
    {
        $cleanableModels->each(function (string $modelClass) {
            $numberOfDeletedRecords = $modelClass::forceCleanUp($modelClass::query())->forceDelete();

            event(new ModelWasCleanedUp($modelClass, $numberOfDeletedRecords));

            $this->info("Deleted {$numberOfDeletedRecords} record(s) from {$modelClass}.");
        });
    }

    protected function getAllModelsFromEachDirectory(array $directories) : Collection
    {
        return collect($directories)
            ->map(function ($directory) {
                return $this->getClassNamesInDirectory($directory)->all();
            })
            ->flatten();
    }

    protected function getClassNamesInDirectory(string $directory) : Collection
    {
        $files = config('model-cleanup.recursive', true)
            ? $this->filesystem->allFiles($directory)
            : $this->filesystem->files($directory);

        return collect($files)->map(function (string $path) {
            return $this->getFullyQualifiedClassNameFromFile($path);
        })->filter(function (string $className) {
            return ! empty($className);
        });
    }

    protected function getFullyQualifiedClassNameFromFile(string $path) : string
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);

        $traverser = new NodeTraverser();

        $traverser->addVisitor(new NameResolver());

        $code = file_get_contents($path);

        $statements = $parser->parse($code);

        $statements = $traverser->traverse($statements);

        return collect($statements[0]->stmts)
            ->filter(function ($statement) {
                return $statement instanceof Class_;
            })
            ->map(function (Class_ $statement) {
                return $statement->namespacedName->toString();
            })
            ->first() ?? '';
    }
}
