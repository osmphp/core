<?php

declare(strict_types=1);

namespace Osm;

use Osm\App\ModuleGroup as BaseModuleGroup;

class ModuleGroup extends BaseModuleGroup
{
    // 0 means that there is a single module in this very directory.
    // It's set to prevent module search in this directory, as there are
    // no actual modules
    public int $depth = 0;
}