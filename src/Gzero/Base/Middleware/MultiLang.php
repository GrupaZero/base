<?php namespace Gzero\Base\Middleware;

use Closure;

class MultiLang {

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request Request object
     * @param \Closure                 $next    Next middleware
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $languages = ['pl', 'en'];

        if (!in_array($request->segment(1), $languages, true)) {
            //app()->setLocale($locale);
            //$this->app['config']['gzero.multilang.detected'] = true;
            return redirect()->to(implode('/', array_prepend($request->segments(), config('app.fallback_locale'))));
        }

        return $next($request);
    }

}
