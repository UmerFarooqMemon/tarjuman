<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    /**
     * Authenticate website/general API requests via env API_TOKEN.
     *
     * Accepts X-API-Token header or Authorization: Bearer {API_TOKEN}.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('api.token', '');

        if ($expected === '') {
            return response()->json([
                'message' => 'API token is not configured.',
            ], 401);
        }

        $provided = $request->header('X-API-Token')
            ?: $this->bearerToken($request);

        if (! is_string($provided) || ! hash_equals($expected, $provided)) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        return $next($request);
    }

    protected function bearerToken(Request $request): ?string
    {
        $header = $request->header('Authorization', '');

        if (! is_string($header) || ! str_starts_with($header, 'Bearer ')) {
            return null;
        }

        $token = trim(substr($header, 7));

        return $token !== '' ? $token : null;
    }
}
