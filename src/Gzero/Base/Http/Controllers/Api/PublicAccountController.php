<?php namespace Gzero\Base\Http\Controllers\Api;

use Gzero\Base\Http\Controllers\ApiController;
use Gzero\Base\Jobs\UpdateUser;
use Gzero\Base\Services\UserQueryService;
use Gzero\Base\UrlParamsProcessor;
use Gzero\Base\Transformers\UserTransformer;
use Gzero\Base\Validators\AccountValidator;
use Illuminate\Http\Request;

class PublicAccountController extends ApiController {

    /**
     * @var UserQueryService
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
     * @param UserQueryService $service Query service
     * @param Request          $request Request object
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UserQueryService $service, Request $request)
    {
        if (!$request->has('password')) {
            $this->validator->setData($request->except(['password', 'password_confirmation']));
        }

        $user = $service->getById($request->user()->id);
        $this->authorize('update', $user);
        $input = $this->validator->bind('name', ['user_id' => $user->id])->bind('email', ['user_id' => $user->id])
            ->validate('update');
        $user  = dispatch_now(new UpdateUser($user, $input));
        return $this->respondWithSuccess($user, new UserTransformer());
    }


}
