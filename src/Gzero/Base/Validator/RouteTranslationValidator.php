<?php namespace Gzero\Base\Validator;

class RouteTranslationValidator extends AbstractValidator {

    /**
     * @var array
     */
    protected $rules = [
        'create' => [
            'language_code' => 'required|in:pl,en,de,fr',
            'is_active'     => '',
            'path'          => 'required'
        ]
    ];

    /**
     * @var array
     */
    protected $filters = [
        'path' => 'trim'
    ];
}
