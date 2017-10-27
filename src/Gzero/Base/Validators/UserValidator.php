<?php namespace Gzero\Base\Validators;

class UserValidator extends AbstractValidator {

    /**
     * @var array
     */
    protected $rules = [
        'list'   => [
            'page'     => 'numeric',
            'per_page' => 'numeric',
            'sort'     => '',
        ],
        'update' => [
            'email'      => 'required|email|unique:users,email,@user_id',
            'name'       => 'required|min:3|unique:users,name,@user_id',
            'first_name' => '',
            'last_name'  => ''
        ]
    ];

}
