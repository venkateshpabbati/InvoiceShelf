<?php

namespace App\Platform\Modules\Console;

use App\Platform\Modules\Runtime\ModuleInstaller;
use Illuminate\Console\Command;

/**
 * Finishes an installation whose module files are already sitting on disk.
 */
class InstallModuleCommand extends Command
{
    /** @var string */
    protected $signature = 'install:module {module} {version}';

    /** @var string */
    protected $description = 'Install cloned module.';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Hand the module name and version to the runtime installer.
     */
    public function handle(): int
    {
        $name = $this->argument('module');
        $version = $this->argument('version');

        ModuleInstaller::complete($name, $version);

        return self::SUCCESS;
    }
}
