<?php

declare(strict_types=1);

namespace Osm\Core\Tests;

use Osm\Core\Samples\App;
use Osm\Core\Samples\Some\ObservableObject;
use Osm\Runtime\Apps;
use PHPUnit\Framework\TestCase;

class test_11_events extends TestCase
{
    public function test_accessing_non_existent_key() {
        // GIVEN a compiled sample app
        Apps::compile(App::class);

        Apps::run(Apps::create(App::class), function () {
            // AND an observable object with registered event callbacks
            $runningProcess = $ranProcess = null;
            $object = ObservableObject::new()
                ->on('running', function($process) use (&$runningProcess){
                    $runningProcess = $process;
                })
                ->on('ran', function($result, $process) use (&$ranProcess){
                    $ranProcess = $process;
                    return $result + 5;
                });

            // WHEN you run the logic of the observable object
            $result = $object->run('some_process');

            // THEN all the event callbacks are executed, too
            $this->assertEquals('some_process', $runningProcess);
            $this->assertEquals('some_process', $ranProcess);
            $this->assertEquals(10 + 5, $result);
        });
    }
}