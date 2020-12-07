<?php

namespace Osm\Core\Tests;

use Osm\Core\App;
use Osm\Framework\Testing\Tests\UnitTestCase;

class AppTest extends UnitTestCase
{
    public $module = self::NO_MODULE; // the test class is not bound to a module

    public function test_that_tests_are_executed() {
        $this->assertTrue(true);
    }

    public function test_that_app_is_accessible() {
        global $osm_app; /* @var App $osm_app */

        $this->assertNotNull($osm_app);
    }

    public function test_that_environment_is_loaded() {
        $this->assertEquals('testing', getenv('APP_ENV'));
    }

    public function test_that_packages_are_loaded() {
        global $osm_app; /* @var App $osm_app */

        $this->assertArrayHasKey('osmphp/framework2', $osm_app->packages);
    }

    public function test_that_modules_are_loaded() {
        global $osm_app; /* @var App $osm_app */

        $this->assertArrayHasKey('Osm_Framework_Testing', $osm_app->modules);
    }
}