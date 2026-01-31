<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogSlowRequests
{
    public function handle(Request $request, Closure $next)
    {
        $startedAt = microtime(true);
        $response = $next($request);

        $elapsedMs = (int) ((microtime(true) - $startedAt) * 1000);
        $thresholdMs = (int) env('SLOW_REQUEST_MS', 2000);

        if ($elapsedMs >= $thresholdMs) {
            $route = $request->route();
            Log::warning('Slow request', [
                'method' => $request->method(),
                'path' => $request->path(),
                'route' => $route?->getName(),
                'status' => method_exists($response, 'getStatusCode') ? $response->getStatusCode() : null,
                'elapsed_ms' => $elapsedMs,
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
            ]);
        }

        return $response;
    }
}
