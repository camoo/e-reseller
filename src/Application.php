<?php

declare(strict_types=1);

namespace App;

use App\Di\Module\HttpModule;
use CAMOO\Di\Module\ModuleCollection;

final class Application
{
    public function dependencyInjectionModules(ModuleCollection $moduleCollection): void
    {
        $moduleCollection->add(HttpModule::class);
    }
}
