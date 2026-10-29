<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user()) {
            return redirect()->guest(route('login'));
        }

        if ($request->user()->role !== $role) {
            return match ($request->user()->role) {
                'admin' => redirect()->route('admin.dashboard'),
                'employer' => redirect()->route('employer.jobs.index'),
                default => redirect()->route('jobs.index'),
            };
        }

        return $next($request);
    }
}
