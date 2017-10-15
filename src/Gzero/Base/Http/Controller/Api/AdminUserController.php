<?php namespace Gzero\Base\Http\Controller\Api;

use Gzero\Base\Http\Controller\ApiController;
use Gzero\Base\Model\User;
use Gzero\Base\Service\UserService;
use Gzero\Base\Transformer\UserTransformer;
use Gzero\Base\UrlParamsProcessor;
use Gzero\Base\Validator\UserValidator;
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
     *   operationId="getUsers",
     *   produces={"application/json"},
     *   security={{"Bearer": {}}},
     *   @SWG\Response(response="200", description="successful operation")
     * )
     *
     * @return \Illuminate\Http\JsonResponse
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
        return $this->respondWithSuccess($results, new UserTransformer);
    }

    /**
     * Display the specified resource.
     *
     * @SWG\Get(
     *   path="/admin/users/{id}",
     *   tags={"user"},
     *   summary="Info for a specific user",
     *   operationId="getLanguages",
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
     * @param int $id user id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = $this->userService->getById($id);
        if (!empty($user)) {
            $this->authorize('read', $user);
            return $this->respondWithSuccess($user, new UserTransformer);
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
     *   operationId="deleteUser",
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
     * @param int $id Id of the user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = $this->userService->getById($id);

        if (!empty($user)) {
            $this->authorize('delete', $user);
            $user->delete();
            return $this->respondWithSimpleSuccess(['success' => true]);
        }
        return $this->respondNotFound();
    }

    /**
     * Updates the specified resource in the database.
     *
     * @SWG\Put(path="/admin/users/{id}",
     *   tags={"user"},
     *   summary="Updated user",
     *   description="Updated user",
     *   operationId="updateUser",
     *   produces={"application/json"},
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
     *     @SWG\Schema(ref="#/definitions/User")
     *   ),
     *   @SWG\Response(response=400, description="Invalid user supplied"),
     *   @SWG\Response(response=404, description="User not found")
     * )
     *
     * @param int $id User id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id)
    {
        $user = $this->userService->getById($id);
        if (!empty($user)) {
            $this->authorize('update', $user);
            $input = $this->validator->bind('name', ['user_id' => $user->id])->bind('email', ['user_id' => $user->id])
                ->validate('update');
            $user  = $this->userService->update($user, $input);
            return $this->respondWithSuccess($user, new UserTransformer());
        }
        return $this->respondNotFound();
    }


}
