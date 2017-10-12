<?php namespace Gzero\Base\Http\Controller\Api;

use Gzero\Base\Http\Controller\ApiController;
use Gzero\Base\Service\UserService;
use Gzero\Base\UrlParamsProcessor;
use Gzero\Base\Transformer\UserTransformer;
use Gzero\Base\Validator\AccountValidator;
use Illuminate\Http\Request;

class PublicAccountController extends ApiController {

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * UserController constructor.
     *
     * @param UrlParamsProcessor $processor   Url processor
     * @param UserService        $userService User repository
     * @param AccountValidator   $validator   User validator
     * @param Request            $request     Request object
     */
    public function __construct(
        UrlParamsProcessor $processor,
        UserService $userService,
        AccountValidator $validator,
        Request $request
    ) {
        parent::__construct($processor);
        $this->validator   = $validator->setData($request->all());
        $this->userService = $userService;
    }

    /**
     * Updates the specified resource in the database.
     *
     * @param Request $request Request object
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        if (!$request->has('password')) {
            $this->validator->setData($request->except(['password', 'password_confirmation']));
        }

        $user = $this->userService->getById($request->user()->id);
        $this->authorize('update', $user);
        $input = $this->validator->bind('nick', ['user_id' => $user->id])->bind('email', ['user_id' => $user->id])
            ->validate('update');
        $user  = $this->userService->update($user, $input);
        return $this->respondWithSuccess($user, new UserTransformer());
    }


}
