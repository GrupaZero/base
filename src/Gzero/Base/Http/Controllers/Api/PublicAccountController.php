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
