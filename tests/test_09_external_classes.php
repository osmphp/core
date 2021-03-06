<?php

declare(strict_types=1);

namespace Osm\Core\Tests;

use Osm\Core\Samples\App;
use Osm\Runtime\Apps;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Lock\Store\InMemoryStore;

class test_09_external_classes extends TestCase
{
    public function test_that_applied_advice_gets_called() {
        // GIVEN a compiled sample app
        Apps::compile(App::class);

        Apps::run(Apps::create(App::class), function (App $app) {
            // WHEN you create an instance of a class that is not a part of
            // any module, but that it added to reflection by `Some` module
            $store = $app->create(InMemoryStore::class);

            // THEN you can call methods added with dynamic traits
            $this->assertTrue($store->testMethod());
        });
    }
}