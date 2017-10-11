<?php namespace Gzero\Base\Model;

class Language extends Base {

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $primaryKey = 'code';

    /**
     * @var array
     */
    protected $fillable = [
        'code',
        'i18n',
        'is_enabled',
        'is_default'
    ];

    /**
     * @var array
     */
    protected $attributes = [
        'is_enabled' => false,
        'is_default' => false
    ];

}
