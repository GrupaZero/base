<?php namespace Gzero\Base\Http\Controllers\Api;

use Gzero\Base\Http\Controllers\ApiController;
use Gzero\Base\Transformers\LanguageTransformer;
use Gzero\Base\Service\LanguageService;

class AdminLanguageController extends ApiController {

    /**
     * @var LanguageService
     */
    protected $langService;

    /**
     * LangController constructor
     *
     * @param LanguageService $lang Content repo
     */
    public function __construct(LanguageService $lang)
    {
        $this->langService = $lang;
    }

    /**
     * Display a listing of the resource.
     *
     * @SWG\Get(
     *   path="/admin/languages",
     *   tags={"language"},
     *   summary="List languages",
     *   operationId="getLanguages",
     *   produces={"application/json"},
     *   security={{"Bearer": {}}},
     *   @SWG\Response(response="200", description="successful operation")
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->respondWithSuccess($this->langService->getAll(), new LanguageTransformer);
    }

    /**
     * Display the specified resource.
     *
     * @SWG\Get(
     *   path="/admin/languages/{code}",
     *   tags={"language"},
     *   summary="Info for a specific language",
     *   operationId="getLanguageByCode",
     *   produces={"application/json"},
     *   security={{"Bearer": {}}},
     *   @SWG\Parameter(
     *     name="code",
     *     in="path",
     *     description="language code that need to be returned",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(response="200", description="successful operation")
     * )
     *
     * @param int $code Lang code
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($code)
    {
        $lang = $this->langService->getByCode($code);
        if (empty($lang)) {
            return $this->respondNotFound();
        }
        return $this->respondWithSuccess($lang, new LanguageTransformer);
    }

}
