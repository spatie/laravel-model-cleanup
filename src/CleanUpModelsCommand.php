<?php

namespace Spatie\DatabaseCleanup;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use PhpParser\Error;
use PhpParser\Node\Stmt\Class_;
use PhpParser\ParserFactory;
use ClassPreloader\Parser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;

class CleanUpModelsCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'databaseCleanup:clean';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all expired records from all chosen tables.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $config = config('laravel-database-cleanup');

        if (!empty($config['models'])) {
            $this->deleteExpiredRecords(collect($config['models']));
        }
        if (!empty($config['directories'])) {
            $this->deleteExpiredRecords($this->filterOutOnlyCleanableModels($config['directories']));
        }
    }

    private function filterOutOnlyCleanableModels(array $directory) : Collection
    {
        return $this->getAllModelClassNames($directory)->filter(function ($modelClass) {

            return in_array(GetsCleanedUp::class, class_implements($modelClass));
        });
    }

    private function getAllModelClassNames(array $directory) : Collection
    {
        return collect(File::files($directory['models']))->map(function ($path) {

            $modelClass = $this->getClassFromFile($path);

            return $modelClass;

        });
    }

    private function deleteExpiredRecords(Collection $models)
    {
        collect($models)->each(function (string $class) {

            return $class::cleanUpModels($class::query())->delete();

        });
    }

    private function getClassFromFile(string $path) : string
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $traverser = new NodeTraverser();

        // add your visitor
        $traverser->addVisitor(new NameResolver());

        try {
            $code = file_get_contents($path);

            // parse
            $statements = $parser->parse($code);

            // traverse
            $statements = $traverser->traverse($statements);

            return collect($statements[0]->stmts)
                ->filter(function ($statement) {
                    return $statement instanceof Class_;
                })
                ->map(function (Class_ $statement) {
                    return $statement->namespacedName->toString();
                })
                ->first();
        } catch (Error $e) {
            echo 'Parse Error: ', $e->getMessage();
        }
    }
}
