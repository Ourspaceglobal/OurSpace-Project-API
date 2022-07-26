<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class AuthenticateTwofa
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        return !$request->user()->tokenCan('*')
            ? ResponseBuilder::asError(101)
                ->withHttpCode(Response::HTTP_UNAUTHORIZED)
                ->withMessage(trans('auth.requires_2fa'))
                ->build()
            : $next($request);
    }
}
