<?php

namespace Spatie\DatabaseCleanup;

use Illuminate\Console\Command;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem as File;
use PhpParser\Error;
use PhpParser\Node\Stmt\Class_;
use PhpParser\ParserFactory;
use ClassPreloader\Parser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use Symfony\Component\Debug\Exception\FatalErrorException;

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

    protected $app;

    public function __construct(Application $app)
    {
        parent::__construct();
        $this->app = $app;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $models = collect(config('laravel-database-cleanup.models'));

        $directories = config('laravel-database-cleanup.directories');

        $this->cleanUpModels($models);

        $this->cleanUpDirectories($directories);

    }

    protected function cleanUpModels($models)
    {
        if (!empty($models)) {
            $this->comment('Start deleting models.');
            try{
                $this->deleteExpiredRecords($models);
            }
            catch(FatalErrorException $error){

                $this->error('Something went wrong: ' .$error->getMessage());
            }
        }
    }

    protected function cleanUpDirectories($directories)
    {
        if (!empty($directories)) {
            $this->comment('Start deleting directories.');
            try{
                $this->deleteExpiredRecords($this->filterOutOnlyCleanableModels($directories));
            }
            catch(FatalErrorException $error){
                $this->error('Something went wrong: ' .$error->getMessage());
            }
        }
    }

    protected function filterOutOnlyCleanableModels(array $directory) : Collection
    {
        return $this->getAllModelClassNames($directory)->filter(function ($modelClass) {

            return in_array(GetsCleanedUp::class, class_implements($modelClass));
        });
    }

    protected function getAllModelClassNames(array $directory) : Collection
    {
        $fileClass = $this->app->make(File::class);

        return collect($fileClass->files($directory['models']))->map(function ($path) {

            $modelClass = $this->getClassFromFile($path);

            return $modelClass;

        });
    }

    protected function deleteExpiredRecords(Collection $models)
    {
        collect($models)->each(function (string $class) {

            return $class::cleanUpModel($class::query())->delete();

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
            $this->error('Parse Error: '. $error->getMessage());
        }
    }

}
