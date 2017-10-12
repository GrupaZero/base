<?php namespace Gzero\Base\Validator;

class RouteTranslationValidator extends AbstractValidator {

    /**
     * @var array
     */
    protected $rules = [
        'create' => [
            'lang_code' => 'required|in:pl,en,de,fr',
            'is_active' => '',
            'url'       => 'required'
        ]
    ];

    /**
     * @var array
     */
    protected $filters = [
        'url' => 'trim'
    ];
}
