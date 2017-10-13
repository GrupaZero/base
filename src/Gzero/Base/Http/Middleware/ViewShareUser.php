<?php namespace Gzero\Base\Http\Middleware;

use Gzero\Base\Model\GuestUser;
use Closure;

class ViewShareUser {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth()->check()) {
            view()->share('user', auth()->user());
        } else {
            view()->share('user', new GuestUser());
        }

        return $next($request);
    }
}