<?php

declare(strict_types=1);

use Osm\Runtime\Apps;

require 'vendor/autoload.php';

Apps::$project_path = getcwd();
Apps::hint(ltrim($argv[1], '\\'));
