<?php

namespace Spatie\DatabaseCleanup;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
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

    protected $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment('Start cleaning up models.');

        $models = $this->getAllModels();

        $this->cleanUp($models);

    }

    protected function getAllModels() : Collection
    {
        $directories = config('laravel-database-cleanup.directories');
        $modelsClasses = config('laravel-database-cleanup.models');

        $modelsFromDirectories = $this->getAllModelsOfEachDirectory($directories);

        $allModels = $modelsFromDirectories
            ->merge(collect($modelsClasses))
            ->flatten();

        return $allModels;
    }


    protected function cleanUp(Collection $collections)
    {
        $cleanables = $this->filterOutOnlyCleanableModels($collections);

        return $this->cleanExpiredRecords($cleanables);
    }


    protected function filterOutOnlyCleanableModels(Collection $collections) : Collection
    {
        return $collections->filter(function ($modelClass) {

            return in_array(GetsCleanedUp::class, class_implements($modelClass));

        });
       ;
    }

    protected function cleanExpiredRecords(Collection $models)
    {
        $models->each(function(string $class){

            $query = $class::cleanUpModel($class::query());

            $count  = $query->count();

            $query->delete();

            $this->comment("Model {$query} is got cleaned. {$count} records have been deleted.");

        });
    }


    protected function getAllModelsOfEachDirectory(array $directories) : Collection
    {
        return collect($directories)->map(function($directory){

            return $this->getAllModelClassNames($directory)->all();

        });
    }

    protected function getAllModelClassNames(string $directory) : Collection
    {
        return collect($this->filesystem->files($directory))->map(function ($path) {

            return $this->getClassFromFile($path);
        });
    }

    protected function getClassFromFile(string $path) : string
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
        } catch (Error $error) {
            $this->error('Parse Error: '.$error->getMessage());
        }
    }


}
