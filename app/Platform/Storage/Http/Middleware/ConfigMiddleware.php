<?php

namespace App\Platform\Storage\Http\Middleware;

use App\Platform\Operations\Installation\Application\InstallationState;
use App\Platform\Storage\Models\FileDisk;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets a request name the storage target it wants to work against.
 *
 * A `file_disk_id` anywhere in the input -- query string or body -- points at a
 * registered disk, and that disk's credentials are pushed into the runtime
 * filesystem configuration before the route runs. It is what makes the backup
 * endpoints able to list, fetch and delete archives on a disk other than the
 * default one without any of them configuring anything themselves.
 *
 * Two silences are deliberate. An id that matches no row is ignored rather than
 * refused, so the request carries on against whatever disk was already
 * configured; and the whole step is skipped until the installer has built the
 * schema, because there is no table to read a disk from before that. Nothing
 * happens here for a request that names no disk either -- the default disk is
 * registered once during application bootstrap.
 *
 * The switch is global: this runs on every request the application handles, not
 * just the storage routes.
 */
class ConfigMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (InstallationState::isDbCreated() && $request->has('file_disk_id')) {
            $requested = FileDisk::find($request->file_disk_id);

            $requested?->setConfig();
        }

        return $next($request);
    }
}
