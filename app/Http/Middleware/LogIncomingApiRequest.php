<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Writes one structured JSON line per API request to STDOUT (Docker / `docker compose logs -f app`).
 * No request/response bodies — avoids passwords/tokens in logs.
 */
final class LogIncomingApiRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $started = hrtime(true);

        /** @var Response $response */
        $response = $next($request);

        $durationMs = (int) round((hrtime(true) - $started) / 1_000_000);

        $query = $request->query();
        $context = [
            'method' => $request->getMethod(),
            'path' => '/'.$request->path(),
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
            'status' => $response->getStatusCode(),
            'duration_ms' => $durationMs,
        ];

        if ($query !== []) {
            $context['query'] = $query;
        }

        $line = 'HTTP API '.json_encode($context, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
        if (\defined('STDOUT')) {
            \fwrite(\STDOUT, $line);
        } else {
            file_put_contents('php://stdout', $line);
        }

        return $response;
    }
}
