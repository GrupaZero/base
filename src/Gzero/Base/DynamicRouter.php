<?php namespace Gzero\Base;

use Gzero\Base\Events\RouteMatched;
use Gzero\Base\Models\Language;
use Gzero\Base\Models\Route;
use Gzero\Base\Repositories\RouteReadRepository;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DynamicRouter {

    /**
     * @var RouteReadRepository
     */
    protected $repository;

    /**
     * @var Gate
     */
    protected $gate;

    /**
     * DynamicRouter constructor
     *
     * @param RouteReadRepository $query RouteQuery service
     * @param Gate                $gate  Gate
     */
    public function __construct(RouteReadRepository $query, Gate $gate)
    {
        $this->repository = $query;
        $this->gate       = $gate;
    }

    /**
     * Handles dynamic content rendering
     *
     * @param Request  $request  Request
     * @param Language $language Lang entity
     *
     * @throws NotFoundHttpException
     * @return Response
     */
    public function handleRequest(Request $request, Language $language)
    {
        $uri   = $this->getRequestedPath($request, $language);
        $route = $this->repository->getByPath($uri, $language->code);

        if (!$this->routeCanBeShown($route, $language)) {
            throw new NotFoundHttpException();
        }
        if ($route->getRoutable() === null) {

            throw new NotFoundHttpException();
        }

        event(new RouteMatched($route, $request));

        return $route->getRoutable()->handle($route, $language);
    }

    /**
     * @param Request  $request  Request object
     * @param Language $language Language object
     *
     * @return string
     */
    protected function getRequestedPath(Request $request, Language $language)
    {
        $segments = $request->segments();
        if (!$language->isDefault()) {
            array_shift($segments);
        }
        return implode('/', $segments);
    }

    /**
     * @param Route|null $route    Route Object
     * @param Language   $language Language object
     *
     * @return bool
     */
    protected function routeCanBeShown($route, Language $language): bool
    {
        return !empty($route) && ($route->hasActiveTranslation($language->code) || $this->gate->allows('viewInactive', $route));
    }

}
