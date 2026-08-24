<?php

namespace App\Platform\Operations\Http\Webhooks;

use App\Platform\Http\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/**
 * Webhook that lets an external scheduler drive Laravel's own scheduler on
 * installs that cannot register a system cron entry.
 */
class CronJobController extends Controller
{
    /**
     * Run the due scheduled tasks. The shared-token check has already been
     * made by the route middleware, so nothing is read off the request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        Artisan::call('schedule:run');

        return response()->json(['success' => true]);
    }
}
