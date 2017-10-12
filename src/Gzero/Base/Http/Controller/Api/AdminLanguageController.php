<?php namespace Gzero\Base\Http\Controller\Api;

use Gzero\Base\Http\Controller\ApiController;
use Gzero\Base\Transformer\LangTransformer;
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->respondWithSuccess($this->langService->getAll(), new LangTransformer);
    }

    /**
     * Display the specified resource.
     *
     * @param int $code Lang code
     *
     * @return Response
     */
    public function show($code)
    {
        $lang = $this->langService->getByCode($code);
        if (empty($lang)) {
            return $this->respondNotFound();
        }
        return $this->respondWithSuccess($lang, new LangTransformer);
    }

}

/*
|--------------------------------------------------------------------------
| START API DOCS
|--------------------------------------------------------------------------
*/

/**
 * @api                 {get} /langs 1. GET collection of entities
 * @apiVersion          0.1.0
 * @apiName             GetLangList
 * @apiGroup            Language
 * @apiPermission       admin
 * @apiDescription      Get all languages
 * @apiUse              LangCollection
 *
 * @apiExample          Example usage:
 * curl -i http://api.example.com/v1/langs
 * @apiSuccessExample   Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "data": [
 *            {
 *              "code": "en",
 *              "i18n": "en_US",
 *              "isEnabled": false,
 *              "isDefault": true
 *            },
 *            {
 *              "code": "pl",
 *              "i18n": "pl_PL",
 *              "isEnabled": false,
 *              "isDefault": false
 *            }
 *       ]
 *     }
 */

/**
 * @api                 {get} /langs/:code 2. GET single entity
 * @apiVersion          0.1.0
 * @apiName             GetLang
 * @apiGroup            Language
 * @apiPermission       admin
 * @apiDescription      Get a single language by passing lang code
 * @apiParam {String}   code Lang unique code
 * @apiUse              Lang
 *
 * @apiExample          Example usage:
 * curl -i http://api.example.com/v1/langs/en
 * @apiSuccessExample   Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "code": "en",
 *       "i18n": "en_US",
 *       "isEnabled": false,
 *       "isDefault": true
 *     }
 */

/**
 * @apiDefine Lang
 * @apiSuccess {String} code Lang code
 * @apiSuccess {String} i18n Lang i18n code
 * @apiSuccess {Boolean} is_enabled Flag if language is enabled
 * @apiSuccess {Boolean} is_default Flag if language is default
 */

/**
 * @apiDefine LangCollection
 * @apiSuccess {Array[]} data Array of Languages
 * @apiSuccess {String} data.code Lang code
 * @apiSuccess {String} data.i18n Lang i18n code
 * @apiSuccess {Boolean} data.is_enabled Flag if language is enabled
 * @apiSuccess {Boolean} data.is_default Flag if language is default
 */

/*
|--------------------------------------------------------------------------
| END API DOCS
|--------------------------------------------------------------------------
*/
