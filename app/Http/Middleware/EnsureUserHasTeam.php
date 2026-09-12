<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasTeam
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && ! $request->user()->team()->exists()) {
            return redirect()->route('team.create')
                ->with('warning', 'Please create your racing team first to start playing.');
        }

        return $next($request);
    }
}
