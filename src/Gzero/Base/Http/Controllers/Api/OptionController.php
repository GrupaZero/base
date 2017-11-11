<?php namespace Gzero\Base\Http\Controllers\Api;

use Gzero\Base\Http\Controllers\ApiController;
use Gzero\Base\Models\Option;
use Gzero\Base\Http\Resources\Option as OptionResource;
use Gzero\Base\Http\Resources\OptionCollection;
use Gzero\Base\Http\Resources\OptionCategoryCollection;
use Gzero\Base\Services\OptionService;
use Gzero\Base\Services\RepositoryValidationException;
use Gzero\Base\Validators\OptionValidator;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OptionController extends ApiController {

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
     *   path="/options",
     *   tags={"options", "public"},
     *   summary="Get all option categories",
     *   description="Retrieves a list of all available option categories.",
     *   produces={"application/json"},
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/OptionCategory")),
     *  )
     * )
     *
     * @return OptionCategoryCollection
     */
    public function index()
    {
        return new OptionCategoryCollection($this->optionService->getCategories());
    }

    /**
     * Display all options from selected category.
     *
     * @SWG\Get(
     *   path="/options/{category}",
     *   tags={"options", "public"},
     *   summary="Get all options from selected category, returned as key, value pairs for each available language.",
     *   description="Retrieves a list of all available options from specified category.",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="category",
     *     in="path",
     *     description="Category key that need to be returned",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/OptionResponse")),
     *  ),
     *   @SWG\Response(response=404, description="Category not found")
     * )
     *
     * @param string $key option category key
     *
     * @return OptionCollection
     */
    public function show($key)
    {
        try {
            $option = $this->optionService->getOptions($key);
            return new OptionCollection($option);
        } catch (RepositoryValidationException $e) {
            return $this->respondWithError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Updates the specified resource in the database.
     *
     * @SWG\Put(
     *   path="/options/{category}",
     *   tags={"options"},
     *   summary="Updates selected option within the given category",
     *   description="Updates specified option for the given category, <b>'admin-access'</b> policy is required.",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="category",
     *     in="path",
     *     description="Category key that the updated option belongs to",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="option",
     *     in="body",
     *     description="Option that we want to update with value for each available language..",
     *     required=true,
     *     @SWG\Schema(
     *       type="object",
     *       required={"key, value"},
     *       @SWG\Property(
     *         property="key",
     *         type="string",
     *         example="example_key"
     *       ),
     *       @SWG\Property(
     *         property="value",
     *         type="array",
     *         example="['en' => null,'pl' => null,'de' => null,'fr' => null]",
     *         @SWG\Items(type="string"),
     *       )
     *     )
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(type="object", ref="#/definitions/Option"),
     *   ),
     *   @SWG\Response(response=404, description="Category not found")
     * )
     *
     * @param string $categoryKey option category key
     *
     * @return OptionResource
     * @throws ValidationException
     *
     */
    public function update($categoryKey)
    {
        $input = $this->validator->validate('update');
        $this->authorize('update', [Option::class, $categoryKey]);
        try {
            $this->optionService->updateOrCreateOption($categoryKey, $input['key'], $input['value']);
            return new OptionResource($this->optionService->getOption($categoryKey, $input['key']));
        } catch (RepositoryValidationException $e) {
            return $this->respondWithError($e->getMessage(), $e->getCode());
        }
    }
}
