<?php

namespace App\Platform\Modules\Runtime;

use App\Platform\Modules\Events\ModuleEnabledEvent;
use App\Platform\Modules\Events\ModuleInstalledEvent;
use App\Platform\Modules\Models\Module as ModelsModule;
use Illuminate\Support\Facades\Artisan;
use Nwidart\Modules\Facades\Module;

class ModuleInstaller
{
    /**
     * Migrate and activate a module already present on disk, write its registry
     * row, then announce the install and the activation.
     */
    public static function complete($module, $version): bool
    {
        Module::register();

        Artisan::call(sprintf('module:migrate %s --force', $module));
        Artisan::call(sprintf('module:enable %s', $module));

        $record = ModelsModule::updateOrCreate(
            ['name' => $module],
            ['version' => $version, 'installed' => true, 'enabled' => true]
        );

        event(new ModuleInstalledEvent($record));
        event(new ModuleEnabledEvent($record));

        return true;
    }
}
