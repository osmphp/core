<?php

declare(strict_types=1);

namespace Osm\Core\Tests;

use Osm\Core\Samples\App;
use Osm\Core\Samples\Attributes\Marker;
use Osm\Core\Samples\Attributes\Repeatable;
use Osm\Core\Samples\Some\Other;
use Osm\Core\Samples\Some\Some;
use Osm\Runtime\Apps;
use Osm\Runtime\Compilation\Compiler;
use PHPUnit\Framework\TestCase;

class test_05_serialization extends TestCase
{
    public function test_app_serialization() {
        // GIVEN a compiler configured to compile a sample app
        $compiler = Compiler::new(['app_class_name' => App::class]);

        // CompilerApp::serialize() calls are done in the context
        // of the global compiler object
        Apps::run($compiler, function(Compiler $compiler) {
            // WHEN you serialize the app object
            $serialized = null;

            // serialize and underlying Object::__sleep() calls are done in
            // the context of the global app object being serialized
            Apps::run($compiler->app->serialize(), function(App $app) use (&$serialized){
                $serialized = serialize($app);
            });

            // AND unserialize it back
            // checks on unserialized objects are done in the context of
            // the global unserialized app
            Apps::run(unserialize($serialized), function(App $app){
                // THEN its #[Serialized] properties are preserved
                $this->assertEquals(App::class, $app->class_name);
            });

        });
    }
}