<?php namespace Gzero\Base\Http\Controllers\Api;

use Gzero\Base\Http\Controllers\ApiController;
use Gzero\Base\Jobs\UpdateUser;
use Gzero\Base\Repositories\UserReadRepository;
use Gzero\Base\UrlParamsProcessor;
use Gzero\Base\Http\Resources\User as UserResource;
use Gzero\Base\Validators\AccountValidator;
use Illuminate\Http\Request;

class PublicAccountController extends ApiController {

    /**
     * @var UserReadRepository
     */
    protected $service;

    /**
     * @var $this
     */
    protected $validator;

    /**
     * UserController constructor.
     *
     * @param UrlParamsProcessor $processor Url processor
     * @param AccountValidator   $validator User validator
     * @param Request            $request   Request object
     */
    public function __construct(UrlParamsProcessor $processor, AccountValidator $validator, Request $request)
    {
        parent::__construct($processor);
        $this->validator = $validator->setData($request->all());
    }

    /**
     * Updates the specified resource in the database.
     *
     * @SWG\Patch(path="/users/{id}",
     *   tags={"user"},
     *   summary="Updated user",
     *   description="Updated user",
     *   produces={"application/json"},
     *   security={{"Bearer": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID of user that needs to be updated",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="body",
     *     description="Updated user object",
     *     required=true,
     *     @SWG\Schema(
     *       type="object",
     *       required={"email, name"},
     *       @SWG\Property(property="email", type="string"),
     *       @SWG\Property(property="name", type="string"),
     *       @SWG\Property(property="first_name", type="string"),
     *       @SWG\Property(property="last_name", type="string"),
     *       @SWG\Property(property="password", type="string"),
     *       @SWG\Property(property="password_confirmation", type="string"),
     *     )
     *   ),
     *   @SWG\Response(response=400, description="Invalid user supplied"),
     *   @SWG\Response(response=404, description="User not found")
     * )
     *
     * @param UserReadRepository $repository Query service
     * @param Request            $request    Request object
     *
     * @return UserResource
     */
    public function update(UserReadRepository $repository, Request $request)
    {
        if (!$request->has('password')) {
            $this->validator->setData($request->except(['password', 'password_confirmation']));
        }

        $user = $repository->getById($request->user()->id);
        $this->authorize('update', $user);
        $input = $this->validator->bind('name', ['user_id' => $user->id])->bind('email', ['user_id' => $user->id])
            ->validate('update');
        $user  = dispatch_now(new UpdateUser($user, $input));
        return new UserResource($user);
    }


}
