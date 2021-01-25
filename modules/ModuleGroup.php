<?php

declare(strict_types=1);

namespace Osm\Core;

use Osm\Core\Base\ModuleGroup as BaseModuleGroup;

class ModuleGroup extends BaseModuleGroup
{
    // 0 means that there is a single module in this very directory
    public int $depth = 0;
}