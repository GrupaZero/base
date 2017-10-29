<?php namespace Gzero\Base\Validators;

class AccountValidator extends AbstractValidator {

    /**
     * @var array
     */
    protected $rules = [
        'update' => [
            'email'                 => 'required|email|unique:users,email,@user_id',
            'name'                  => 'required|min:3|unique:users,name,@user_id',
            'first_name'            => 'min:2|regex:/^([^0-9]*)$/', // without numbers
            'last_name'             => 'min:2|regex:/^([^0-9]*)$/', // without numbers
            'password'              => 'sometimes|min:6|same:password_confirmation|required_with:password_confirmation',
            'password_confirmation' => 'sometimes|min:6|same:password|required_with:password',
        ],
    ];
}