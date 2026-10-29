<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->guest(route('login'));
        }

        if (!$request->user()->isAdmin()) {
            return redirect()->route('jobs.index');
        }

        return $next($request);
    }
}
