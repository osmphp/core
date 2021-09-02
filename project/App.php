<?php

declare(strict_types=1);

namespace Osm\Project;

use Osm\Core\App as BaseApp;

class App extends BaseApp
{
    public static bool $load_all = true;
    public static bool $load_dev_sections = true;
}