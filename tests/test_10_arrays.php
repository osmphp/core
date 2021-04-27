<?php

declare(strict_types=1);

namespace Osm\Core\Tests;

use Osm\Core\Array_;
use Osm\Core\Exceptions\UndefinedArrayKey;
use PHPUnit\Framework\TestCase;

class test_10_arrays extends TestCase
{
    public function test_accessing_non_existent_key() {
        // GIVEN a checked array
        $array = new Array_(['key' => 'value'], "Unknown key ':key'");

        // THEN an exception is thrown
        $this->expectException(UndefinedArrayKey::class);

        // WHEN you access a non existent key
        $value = $array['non_existent'];
    }

    public function test_array_operations() {
        // GIVEN a checked array
        $array = new Array_(['key' => 'value'], "Unknown key ':key'");

        // WHEN you use it as a built-in array
        // THEN it behaves as such
        $this->assertTrue(isset($array['key']));
        $this->assertFalse(isset($array['non_existent']));
        $this->assertEquals('value', $array['key']);
        $array['key2'] = 'value2';
        $this->assertEquals('value2', $array['key2']);
        unset($array['key2']);
        $this->assertFalse(isset($array['key2']));
    }

    public function test_foreach() {
        // GIVEN a checked array
        $array = new Array_(['key' => 'value'], "Unknown key ':key'");

        // WHEN you iterate it
        // THEN it works just like a built-in array
        foreach ($array as $key => $value) {
            $this->assertEquals('key', $key);
            $this->assertEquals('value', $value);
        }
    }
}