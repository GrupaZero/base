<?php namespace Gzero\Core;

use Gzero\Core\Events\ContentRouteMatched;
use Gzero\Repository\ContentRepository;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Events\Dispatcher;
use Gzero\Base\Model\Lang;
use Gzero\Core\Handler\Content\ContentTypeHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DynamicRouter {

    /**
     * @var ContentRepository
     */
    private $repository;

    /**
     * The events dispatcher
     *
     * @var Dispatcher
     */
    protected $events;

    /**
     * DynamicRouter constructor
     *
     * @param ContentRepository $repository Content repository
     * @param Dispatcher        $events     Events dispatcher
     * @param Gate              $gate       Gate
     */
    public function __construct(ContentRepository $repository, Dispatcher $events, Gate $gate)
    {
        $this->repository = $repository;
        $this->events     = $events;
        $this->gate       = $gate;
    }

    /**
     * Handles dynamic content rendering
     *
     * @param String  $url     Url address
     * @param Lang    $lang    Lang entity
     * @param Request $request Request
     *
     * @throws NotFoundHttpException
     * @return View
     */
    public function handleRequest($url, Lang $lang, Request $request)
    {
        //Get url without query string, so that pagination can work
        $url     = preg_replace('/\?.*/', '', $url);
        $content = $this->repository->getByUrl($url, $lang->code);
        // Only if page is visible on public
        if (empty($content) || (!$content->canBeShown() && $this->gate->denies('viewOnFrontend', $content))) {
            throw new NotFoundHttpException();
        }
        $this->events->fire(new ContentRouteMatched($content, $request));
        $type = $this->resolveType($content->type);
        return $type->load($content, $lang)->render();
    }

    /**
     * Dynamically resolve type of content
     *
     * @param String $typeName Type name
     *
     * @return ContentTypeHandler
     * @throws \ReflectionException
     */
    protected function resolveType($typeName)
    {
        $type = app()->make('content:type:' . $typeName);
        if (!$type instanceof ContentTypeHandler) {
            throw new \ReflectionException("Type: $typeName must implement ContentTypeInterface");
        }
        return $type;
    }
}