<?php namespace Gzero\Base\Http\Controllers\Api;

use Gzero\Base\Http\Controllers\ApiController;
use Gzero\Base\Models\Option;
use Gzero\Base\Services\OptionService;
use Gzero\Base\Services\RepositoryValidationException;
use Gzero\Base\Transformers\OptionCategoryTransformer;
use Gzero\Base\Transformers\OptionTransformer;
use Gzero\Base\Validators\OptionValidator;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
     * @SWG\Get(
     *   path="/admin/options",
     *   tags={"options"},
     *   summary="Get all option categories",
     *   operationId="getOptions",
     *   produces={"application/json"},
     *   security={{"Bearer": {}}},
     *   @SWG\Response(response="200", description="successful operation")
     * )
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
     * @SWG\Get(
     *   path="/admin/options/{category}",
     *   tags={"options"},
     *   summary="Get all options from selected category",
     *   operationId="getOptionsByCategory",
     *   produces={"application/json"},
     *   security={{"Bearer": {}}},
     *   @SWG\Parameter(
     *     name="category",
     *     in="path",
     *     description="category key that need to be returned",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(response="200", description="successful operation")
     * )
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
     * @SWG\Put(
     *   path="/admin/options/{category}",
     *   tags={"options"},
     *   summary="Updates selected option within the given category",
     *   operationId="putOptionsByCategory",
     *   produces={"application/json"},
     *   security={{"Bearer": {}}},
     *   @SWG\Parameter(
     *     name="category",
     *     in="path",
     *     description="category key that the updated option belongs to",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="option",
     *     in="body",
     *     description="option that we want to update",
     *     required=true,
     *     @SWG\Schema(ref="#/definitions/Option"),
     *   ),
     *   @SWG\Response(response="200", description="successful operation")
     * )
     *
     * @param string $categoryKey option category key
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
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
