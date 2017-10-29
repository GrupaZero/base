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

class AdminUserController extends ApiController {

    /**
     * @var UserService
     */
    protected $userService;

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
     * Display list of users
     *
     * @SWG\Get(
     *   path="/admin/users",
     *   tags={"user"},
     *   summary="List users",
     *   produces={"application/json"},
     *   security={{"Bearer": {}}},
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

        return new UserCollection($results->setPath(apiUrl('admin/users')));
    }

    /**
     * Display the specified resource.
     *
     * @SWG\Get(
     *   path="/admin/users/{id}",
     *   tags={"user"},
     *   summary="Info for a specific user",
     *   produces={"application/json"},
     *   security={{"Bearer": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID of user that needs to be returned",
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
     * Updates the specified resource in the database.
     *
     * @SWG\Patch(path="/admin/users/{id}",
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
     * Remove the specified user from database.
     *
     * @SWG\Delete(
     *   path="/admin/users/{id}",
     *   tags={"user"},
     *   summary="Delete user",
     *   produces={"application/json"},
     *   security={{"Bearer": {}}},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID of user that needs to be deleted",
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
