<?php

declare(strict_types=1);

use Osm\Runtime\Apps;
use function Osm\handle_errors;

require 'vendor/autoload.php';
handle_errors();

Apps::$project_path = getcwd();
Apps::compile(ltrim($argv[1], '\\'));
