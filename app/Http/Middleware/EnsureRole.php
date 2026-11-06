<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $allowed = array_filter(array_map(fn (string $role) => Role::tryFrom($role), $roles));

        if (! $request->user()?->hasRole(...$allowed)) {
            abort(403);
        }

        return $next($request);
    }
}
