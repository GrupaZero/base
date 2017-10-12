<?php namespace Gzero\Base\Http\Middleware;

use Closure;

class AdminApiAccess {

    /**
     * Return 404 if user is not authenticated or got no admin rights
     *
     * @param \Illuminate\Http\Request $request Request object
     * @param \Closure                 $next    Next middleware
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->user()->hasPermission('admin-api-access') || $request->user()->isSuperAdmin()) {
            return $next($request);
        }
        return abort(404);
    }
}
