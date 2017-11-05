<?php namespace Gzero\Base\Http\Controllers\Api;

use Gzero\Base\Http\Controllers\ApiController;
use Gzero\Base\Http\Resources\User as UserResource;
use Gzero\Base\Http\Resources\UserCollection;
use Gzero\Base\Jobs\DeleteUser;
use Gzero\Base\Jobs\UpdateUser;
use Gzero\Base\Models\User;
use Gzero\Base\Repositories\UserReadRepository;
use Gzero\Base\Services\UserService;
use Gzero\Base\UrlParamsProcessor;
use Gzero\Base\Validators\UserValidator;
use Illuminate\Http\Request;

class UserController extends ApiController {

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var UserValidator
     */
    protected $validator;

    /**
     * UserController constructor.
     *
     * @param UrlParamsProcessor $processor   Url processor
     * @param UserService        $userService User repository
     * @param UserValidator      $validator   User validator
     * @param Request            $request     Request object
     */
    public function __construct(
        UrlParamsProcessor $processor,
        UserService $userService,
        UserValidator $validator,
        Request $request
    ) {
        parent::__construct($processor);
        $this->validator   = $validator->setData($request->all());
        $this->userService = $userService;
    }

    /**
     * Display list of all users
     *
     * @SWG\Get(
     *   path="/users",
     *   tags={"user"},
     *   summary="List of all users",
     *   description="List of all available users, <b>'admin-access'</b> policy is required.",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Response(response="200", description="successful operation")
     * )
     *
     * @return UserCollection
     */
    public function index()
    {
        $this->authorize('readList', User::class);
        $input   = $this->validator->validate('list');
        $params  = $this->processor->process($input)->getProcessedFields();
        $results = $this->userService->getUsers(
            $params['filter'],
            $params['orderBy'],
            $params['page'],
            $params['perPage']
        );

        return new UserCollection($results->setPath(apiUrl('users')));
    }

    /**
     * Display the specified resource.
     *
     * @SWG\Get(
     *   path="/users/{id}",
     *   tags={"user"},
     *   summary="Get specific user",
     *   description="Returns a specific user by id, <b>'admin-access'</b> policy is required.",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID of user that needs to be returned.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(response="200", description="successful operation")
     * )
     *
     * @param UserReadRepository $repository Query service
     * @param int                $id         user id
     *
     * @return UserResource
     */
    public function show(UserReadRepository $repository, $id)
    {
        $user = $repository->getById($id);
        if (!empty($user)) {
            $this->authorize('read', $user);
            return new UserResource($user);
        }
        return $this->respondNotFound();
    }

    /**
     * Updates the specified resource.
     *
     * @SWG\Patch(path="/users/{id}",
     *   tags={"user"},
     *   summary="Updated specific user",
     *   description="Updates specified user, <b>'admin-access'</b> policy is required.",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID of user that needs to be updated.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="body",
     *     description="Fields to update.",
     *     required=true,
     *     @SWG\Schema(
     *       type="object",
     *       required={"email, name"},
     *       @SWG\Property(property="email", type="string"),
     *       @SWG\Property(property="name", type="string"),
     *       @SWG\Property(property="first_name", type="string"),
     *       @SWG\Property(property="last_name", type="string"),
     *     )
     *   ),
     *   @SWG\Response(response=400, description="Invalid user supplied"),
     *   @SWG\Response(response=404, description="User not found")
     * )
     *
     * @param UserReadRepository $repository Query service
     * @param int                $id         User id
     *
     * @return UserResource
     */
    public function update(UserReadRepository $repository, $id)
    {
        $user = $repository->getById($id);
        if (!empty($user)) {
            $this->authorize('update', $user);
            $input = $this->validator
                ->bind('name', ['user_id' => $user->id])
                ->bind('email', ['user_id' => $user->id])
                ->validate('update');
            $user  = dispatch_now(new UpdateUser($user, $input));
            return new UserResource($user);
        }
        return $this->respondNotFound();
    }

    /**
     * Updates the specified resource in the database.
     *
     * @SWG\Patch(path="/users/me",
     *   tags={"user"},
     *   summary="Updated current user",
     *   description="Updates currently logged in user.",
     *   produces={"application/json"},
     *   security={{"Auth": {}}, {"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     in="body",
     *     name="body",
     *     description="Fields to update, <b>'password'</b> is not required, if provided it must match <b>'password_confirmation'</b>.",
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
    public function updateMe(UserReadRepository $repository, Request $request)
    {
        if (!$request->has('password')) {
            $this->validator->setData($request->except(['password', 'password_confirmation']));
        }

        $user = $repository->getById($request->user()->id);
        $this->authorize('update', $user);
        $input = $this->validator->bind('name', ['user_id' => $user->id])->bind('email', ['user_id' => $user->id])
            ->validate('updateMe');
        $user  = dispatch_now(new UpdateUser($user, $input));
        return new UserResource($user);
    }

    /**
     * Remove the specified user from database.
     *
     * @SWG\Delete(
     *   path="/users/{id}",
     *   tags={"user"},
     *   summary="Delete specific user",
     *   description="Deletes specified user, <b>'admin-access'</b> policy is required.",
     *   produces={"application/json"},
     *   security={{"AdminAccess": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID of user that needs to be deleted.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(response="200", description="successful operation")
     * )
     *
     * @param UserReadRepository $repository Query service
     * @param int                $id         Id of the user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(UserReadRepository $repository, $id)
    {
        $user = $repository->getById($id);

        if (!empty($user)) {
            $this->authorize('delete', $user);
            dispatch_now(new DeleteUser($user));
            return $this->respondWithSimpleSuccess(['success' => true]);
        }
        return $this->respondNotFound();
    }

}
