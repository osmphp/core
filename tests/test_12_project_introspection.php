<?php

declare(strict_types=1);

namespace Osm\Core\Tests;

use Osm\Core\Samples\Some\Some;
use Osm\Project\App;
use Osm\Core\Samples\Some\ObservableObject;
use Osm\Runtime\Apps;
use PHPUnit\Framework\TestCase;

class test_12_project_introspection extends TestCase
{
    public function test_class_introspection() {
        // GIVEN a compiled sample app
        Apps::compile(App::class);

        // WHEN you introspect classes in the context of `Osm_Project`
        // application
        $classNames = [];
        Apps::run(Apps::create(App::class), function (App $app)
            use (&$classNames)
        {
            $classNames = array_keys($app->classes);
        });

        // THEN you find classes from any module
        $this->assertContains(Some::class, $classNames);
    }
}