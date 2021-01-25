<?php

declare(strict_types=1);

namespace Osm {
    function make_dir($dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        return $dir;
    }

    function make_dir_for($filename) {
        make_dir(dirname($filename));
        return $filename;
    }
}

