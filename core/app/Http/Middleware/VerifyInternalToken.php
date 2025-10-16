<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyInternalToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $tokenKey = 'default'): Response
    {
        $token = config("security.tokens.$tokenKey");

        if (!$token) {
            abort(403, 'Internal access token is not configured.');
        }

        $providedToken = $this->resolveProvidedToken($request);

        if (!$providedToken || !hash_equals($token, $providedToken)) {
            abort(403, 'Invalid internal access token.');
        }

        return $next($request);
    }

    protected function resolveProvidedToken(Request $request): ?string
    {
        $headerName = config('security.header', 'X-Internal-Token');

        $token = $request->bearerToken();
        if ($token) {
            return $token;
        }

        $token = $request->header($headerName);
        if ($token) {
            return $token;
        }

        $token = $request->query('token');
        if ($token) {
            return $token;
        }

        return null;
    }
}
