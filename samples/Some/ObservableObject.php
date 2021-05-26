<?php

declare(strict_types=1);

namespace Osm\Core\Samples\Some;

use Osm\Core\Object_;
use Osm\Core\Traits\Observable;

class ObservableObject extends Object_
{
    use Observable;

    public function run(string $process): int {
        // fire an event before the main logic
        $this->fire('running', $process);

        // the main logic
        $result = 10;

        // fore another event after the main logic
        return $this->fireFunction('ran', $result, $process);
    }
}