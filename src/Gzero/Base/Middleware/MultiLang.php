<?php namespace Gzero\Base\Middleware;

use Closure;
use Gzero\Base\Exception;
use Gzero\Base\Service\LanguageService;

class MultiLang {

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

        if (!$languages->contains($request->segment(1))) {
            $defaultLanguage = $languageService->getDefault();
            if (empty($defaultLanguage)) {
                throw new Exception('No default language found');
            }
            return redirect()->to(implode('/', array_prepend($request->segments(), $defaultLanguage->code)), 301);
        }

        return $next($request);
    }

}
