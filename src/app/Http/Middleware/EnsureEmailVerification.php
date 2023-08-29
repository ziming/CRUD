<?php

namespace Backpack\CRUD\app\Http\Middleware;

use Closure;

class EnsureEmailVerification
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // if the route name is not one of the verification process, run the verification middleware
        if (!in_array($request->route()->getName(), ['verification.notice', 'verification.verify', 'verification.send'])) {
            // the Laravel middleware needs the user resolver to be set with the backpack guard
            $userResolver = $request->getUserResolver();
            $request->setUserResolver(function () use ($userResolver) {
                return $userResolver(backpack_guard_name());
            });
            $verifiedMiddleware = new (app('router')->getMiddleware()['verified'])();
            return $verifiedMiddleware->handle($request, $next);
        }
        return $next($request);
    }
}
