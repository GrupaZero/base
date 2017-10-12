<?php namespace Gzero\Base\Validator;

class OptionValidator extends AbstractValidator {

    /**
     * @var array
     */
    protected $rules = [
        'update' => [
            'key'   => ['required', 'exists:options,key'],
            'value' => 'required'
        ]
    ];

}
