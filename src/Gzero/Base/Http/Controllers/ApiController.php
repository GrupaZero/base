<?php namespace Gzero\Base\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;
use Gzero\Base\NewUrlParamsProcessor;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * @SWG\Swagger(
 *   schemes={"https"},
 *   basePath="/v1",
 *   host="api.dev.gzero.pl",
 *   consumes={"application/json"},
 *   produces={"application/json"},
 *   @SWG\Info(
 *     title="GZERO API",
 *     version="1.0.0"
 *   )
 * )
 */

/**
 * @SWG\SecurityScheme(
 *     securityDefinition="Auth",
 *     type="apiKey",
 *     description="Bearer token",
 *     name="Authorization",
 *     in="header"
 *   )
 * @SWG\SecurityScheme(
 *     securityDefinition="AdminAccess",
 *     type="apiKey",
 *     description="Bearer token",
 *     name="Authorization",
 *     in="header"
 *   )
 */

/**
 * @SWG\Tag(
 *   name="language",
 *   description="Everything about app languages"
 *   )
 * ),
 * @SWG\Tag(
 *   name="user",
 *   description="Everything about app users"
 *   ),
 * @SWG\Tag(
 *   name="options",
 *   description="Everything about app options"
 *   )
 * @SWG\Tag(
 *   name="public",
 *   description="Actions that do not require authentication."
 *   )
 * )
 */
class ApiController extends Controller {

    use AuthorizesRequests;

    /**
     * @var NewUrlParamsProcessor
     */
    protected $processor;

    /**
     * ApiController constructor
     *
     * @param NewUrlParamsProcessor $processor Url processor
     */
    public function __construct(NewUrlParamsProcessor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * Return response in json format
     *
     * @param mixed $data    Response data
     * @param int   $code    Response code
     * @param array $headers HTTP headers
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respond($data, $code, array $headers = [])
    {
        return response()->json($data, $code, array_merge($this->defaultHeaders(), $headers));
    }

    /**
     * Return no content response in json format
     *
     * @param array $headers HTTP headers
     *
     * @return mixed
     */
    protected function respondNoContent(array $headers = [])
    {
        return $this->respond(null, SymfonyResponse::HTTP_NO_CONTENT, $headers);
    }

    /**
     * Return server error response in json format
     *
     * @param string $message Custom error message
     * @param int    $code    Error code
     * @param array  $headers HTTP headers
     *
     * @return mixed
     */
    protected function respondWithError(
        $message = 'Bad Request',
        $code = SymfonyResponse::HTTP_BAD_REQUEST,
        array $headers = []
    ) {
        return abort($code, $message, $headers);
    }

    /**
     * Return not found response in json format
     *
     * @param string $message Custom message
     * @param array  $headers HTTP headers
     *
     * @return mixed
     */
    protected function respondNotFound($message = 'Not found', array $headers = [])
    {
        return abort(SymfonyResponse::HTTP_NOT_FOUND, $message, $headers);
    }

    /**
     * Default headers for api response
     *
     * @return array
     */
    protected function defaultHeaders()
    {
        return [];
    }
}
