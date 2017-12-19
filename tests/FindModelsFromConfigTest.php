<?php

namespace Spatie\ModelCleanup\Test;

use Spatie\ModelCleanup\CleanUpModelsCommand;
use Spatie\ModelCleanup\Test\Models\CleanableItem;

class FindModelsFromConfigTest extends TestCase
{
    /** @test */
    public function it_can_find_class_name_from_file()
    {
        $method = self::getMethod(CleanUpModelsCommand::class, 'getFullyQualifiedClassNameFromFile');
        $cmd = new CleanUpModelsCommand(app()->make('files'));

        $className = $method->invokeArgs($cmd, ["./tests/Models/CleanableItem.php"]);

        $this->assertTrue($className !== "");
        
        $className = $method->invokeArgs($cmd, ["./tests/Models/NotAClass.php"]);

        $this->assertTrue($className === "");
    }

    /** @test */
    public function it_can_find_class_names_from_directory()
    {
        $method = self::getMethod(CleanUpModelsCommand::class, 'getClassNamesInDirectory');
        $cmd = new CleanUpModelsCommand(app()->make('files'));

        $classNames = $method->invokeArgs($cmd, ["./tests/Models"]);

        $this->assertContains(CleanableItem::class, $classNames);

        $this->assertNotContains(null, $classNames);
    }

    protected static function getMethod($class, $name)
    {
        $class = new \ReflectionClass($class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}