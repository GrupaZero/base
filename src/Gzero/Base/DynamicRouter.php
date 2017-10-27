<?php namespace Gzero\Base;

use Gzero\Base\Events\RouteMatched;
use Gzero\Base\Model\Language;
use Gzero\Base\Model\Route;
use Gzero\Base\Queries\RouteQuery;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DynamicRouter {

    /**
     * @var RouteQuery
     */
    protected $readRepository;

    /**
     * @var Gate
     */
    protected $gate;

    /**
     * DynamicRouter constructor
     *
     * @param RouteQuery $query RouteQuery service
     * @param Gate       $gate  Gate
     */
    public function __construct(RouteQuery $query, Gate $gate)
    {
        $this->readRepository = $query;
        $this->gate           = $gate;
    }

    /**
     * Handles dynamic content rendering
     *
     * @param Request  $request Request
     * @param Language $lang    Lang entity
     *
     * @throws NotFoundHttpException
     * @return Response
     */
    public function handleRequest(Request $request, Language $lang)
    {
        $uri   = $this->getRequestedUri($request, $lang);
        $route = $this->readRepository->getByUrl($uri, $lang->code);

        if ($this->routeCannotBeShown($route, $lang)) {
            throw new NotFoundHttpException();
        }
        if ($route->getRoutable() === null) {

            throw new NotFoundHttpException();
        }

        event(new RouteMatched($route, $request));

        return $route->getRoutable()->handle($route, $lang);
    }

    /**
     * @param Request  $request Request object
     * @param Language $lang    Language object
     *
     * @return string
     */
    protected function getRequestedUri(Request $request, Language $lang)
    {
        $segments = $request->segments();
        if (!$lang->isDefault()) {
            array_shift($segments);
        }
        return implode('/', $segments);
    }

    /**
     * @param Route|null $route Route Object
     * @param Language   $lang  Language object
     *
     * @return bool
     */
    protected function routeCannotBeShown($route, Language $lang): bool
    {
        return empty($route) || (!$route->hasActiveTranslation($lang->code) && $this->gate->denies('viewInactive'));
    }

}
