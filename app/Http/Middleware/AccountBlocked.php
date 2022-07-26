<?php

namespace App\Http\Middleware;

use App\Exceptions\BlockedAccountException;
use Closure;
use Illuminate\Http\Request;

class AccountBlocked
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        return $request->user()->is_blocked
            ? throw new BlockedAccountException()
            : $next($request);
    }
}
