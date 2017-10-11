<?php namespace Gzero\Base\Model;

class OptionCategory extends Base {

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $primaryKey = 'key';

    /**
     * @var array
     */
    protected $fillable = [
        'key'
    ];

    /**
     * @param $key
     *
     * @return mixed
     */
    public static function getByKey($key)
    {
        return static::where('key', $key)->first();
    }

    /**
     * Options one to many relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function options()
    {
        return $this->hasMany(Option::class, 'category_key');
    }

}
