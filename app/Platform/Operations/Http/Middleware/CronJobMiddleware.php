<?php

namespace App\Platform\Operations\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate for the externally callable cron webhook: the caller proves itself
 * with a shared token carried in a request header.
 */
class CronJobMiddleware
{
    /**
     * Name of the header the external scheduler is expected to send.
     */
    private const TOKEN_HEADER = 'x-authorization-token';

    /**
     * Forward the request only when the presented token matches the one in
     * the configuration; anything else is refused outright.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $presented = $request->header(self::TOKEN_HEADER);

        // An empty (or literally "0") header is treated as no token at all,
        // so it can never match, whatever the configured token happens to be.
        if (! $presented) {
            return $this->refuse();
        }

        return $presented == config('services.cron_job.auth_token')
            ? $next($request)
            : $this->refuse();
    }

    /**
     * The refusal body is a bare JSON array, not an object — callers of the
     * webhook match on the status code, so the shape stays as it is.
     */
    private function refuse(): Response
    {
        return response()->json(['unauthorized'], Response::HTTP_UNAUTHORIZED);
    }
}
