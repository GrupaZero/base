<?php namespace Gzero\Base\Http\Controllers;

use Gzero\Base\DynamicRouter;
use Gzero\Base\Service\LanguageService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RouteController extends Controller {

    /**
     * @param DynamicRouter   $router  DynamicRouter service
     * @param LanguageService $service LanguageService
     * @param Request         $request Request object
     *
     * @return \Illuminate\Http\Response
     */
    public function dynamicRouter(DynamicRouter $router, LanguageService $service, Request $request)
    {
        return $router->handleRequest($request, $service->getCurrent());
    }
}
