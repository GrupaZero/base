<?php namespace Gzero\Base\Http\Controllers\Api;

use Gzero\Base\Http\Controllers\ApiController;
use Gzero\Base\Http\Resources\Language as LanguageResource;
use Gzero\Base\Http\Resources\LanguageCollection;
use Gzero\Base\Services\LanguageService;

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
     * @return LanguageCollection
     */
    public function index()
    {
        return new LanguageCollection($this->langService->getAll());
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
     * @return LanguageResource
     */
    public function show($code)
    {
        $lang = $this->langService->getByCode($code);
        if (empty($lang)) {
            return $this->respondNotFound();
        }
        return new LanguageResource($lang);
    }

}
