<?php namespace Gzero\Base\Http\Controller\Api;

use Gzero\Base\Http\Controller\ApiController;
use Gzero\Base\Model\Option;
use Gzero\Base\Service\OptionService;
use Gzero\Base\Service\RepositoryValidationException;
use Gzero\Base\Transformer\OptionCategoryTransformer;
use Gzero\Base\Transformer\OptionTransformer;
use Gzero\Base\Validator\OptionValidator;
use Illuminate\Http\Request;

class AdminOptionController extends ApiController {

    /**
     * @var OptionService
     */
    protected $optionService;

    /**
     * @var OptionValidator
     */
    protected $validator;

    /**
     * OptionController constructor
     *
     * @param OptionService   $option    Option repo
     * @param OptionValidator $validator validator
     * @param Request         $request   Request object
     */
    public function __construct(OptionService $option, OptionValidator $validator, Request $request)
    {
        $this->validator     = $validator->setData($request->all());
        $this->optionService = $option;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $this->authorize('read', Option::class);
        return $this->respondWithSuccess($this->optionService->getCategories(), new OptionCategoryTransformer);
    }

    /**
     * Display all options from selected category.
     *
     * @param string $key option category key
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($key)
    {
        $this->authorize('read', Option::class);
        try {
            $option = $this->optionService->getOptions($key);
            return $this->respondWithSuccess($option, new OptionTransformer);
        } catch (RepositoryValidationException $e) {
            return $this->respondWithError($e->getMessage());
        }
    }

    /**
     * Updates the specified resource in the database.
     *
     * @param string $categoryKey option category key
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Gzero\Validator\ValidationException
     *
     */
    public function update($categoryKey)
    {
        $input = $this->validator->validate('update');
        $this->authorize('update', [Option::class, $categoryKey]);
        try {
            $this->optionService->updateOrCreateOption($categoryKey, $input['key'], $input['value']);
            return $this->respondWithSuccess($this->optionService->getOptions($categoryKey), new OptionTransformer);
        } catch (RepositoryValidationException $e) {
            return $this->respondWithError($e->getMessage());
        }
    }

}

/*
|--------------------------------------------------------------------------
| START API DOCS
|--------------------------------------------------------------------------
*/

/**
 * @api                 {get} /options 1. GET collection of categories
 * @apiVersion          0.1.0
 * @apiName             GetOptionCategories
 * @apiGroup            Options
 * @apiPermission       admin
 * @apiDescription      Get all option categories
 * @apiUse              OptionCollection
 *
 * @apiExample          Example usage:
 * curl -i http://api.example.com/v1/options
 * @apiSuccessExample   Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "data": [
 *            {
 *              "key": "general"
 *            },
 *            {
 *              "key": "seo"
 *            }
 *       ]
 *     }
 */

/**
 * @api                 {get} /options/:category 2. GET category options
 * @apiVersion          0.1.0
 * @apiName             GetOptions
 * @apiGroup            Options
 * @apiPermission       admin
 * @apiDescription      Get all options within the given category
 * @apiParam {String}   key category unique key
 * @apiUse              Option
 *
 * @apiExample          Example usage:
 * curl -i http://api.example.com/v1/options/general
 * @apiSuccessExample   Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "defaultPageSize": {
 *         "en": 5,
 *         "pl": 5
 *       },
 *       "siteDesc": {
 *         "en": "Content management system.",
 *         "pl": "Content management system."
 *       }
 *       "siteName": {
 *         "en": "G-ZERO CMS",
 *         "pl": "G-ZERO CMS"
 *       },
 *     }
 *
 */

/**
 * @api                 {put} /options/:category 3. PUT category options
 * @apiVersion          0.1.0
 * @apiName             UpdateOptions
 * @apiGroup            Options
 * @apiPermission       admin
 * @apiDescription      Update selected option within the given category
 * @apiParam {String}   key option unique key
 * @apiParam {String}   value option value
 * @apiUse              Option
 *
 * @apiExample          Example usage:
 * curl -i http://api.example.com/v1/options/general
 * @apiSuccessExample   Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "defaultPageSize": {
 *         "en": 5,
 *         "pl": 5
 *       },
 *       "siteDesc": {
 *         "en": "Content management system.",
 *         "pl": "Content management system."
 *       }
 *       "siteName": {
 *         "en": "G-ZERO CMS",
 *         "pl": "G-ZERO CMS"
 *       },
 *     }
 *
 */

/**
 * @apiDefine Option
 * @apiSuccess {obj} data obj of all options in category
 */

/**
 * @apiDefine OptionCollection
 * @apiSuccess {Array[]} data Array of all options categories
 * @apiSuccess {String} data.key option key
 */

/*
|--------------------------------------------------------------------------
| END API DOCS
|--------------------------------------------------------------------------
*/
