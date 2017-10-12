<?php namespace Gzero\Base\Middleware;

use Closure;
use Gzero\Base\Exception;
use Gzero\Base\Service\LanguageService;

class MultiLanguage {

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request Request object
     * @param \Closure                 $next    Next middleware
     *
     * @throws Exception
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /** @var LanguageService $languageService */
        $languageService = resolve(LanguageService::class);
        $languages       = $languageService->getAllEnabled()->pluck('code');
        $language        = $languages->first(function ($code) use ($request) {
            return $code === $request->segment(1);
        });

        if (!empty($language)) {
            app()->setLocale($language);
        } else {
            $defaultLanguage = $languageService->getDefault();
            if (empty($defaultLanguage)) {
                throw new Exception('No default language found');
            }
            app()->setLocale($defaultLanguage->code);
        }

        return $next($request);
    }

}
