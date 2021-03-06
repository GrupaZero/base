<?php namespace Gzero\Base\Models;

class Permission extends Base {

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'acl_permissions';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'category',
        'is_core',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The roles that belong to the permission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(User::class, 'acl_permission_role')->withTimestamps();
    }

}
