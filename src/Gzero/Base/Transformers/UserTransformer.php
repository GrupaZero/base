<?php namespace Gzero\Base\Transformers;

use Gzero\Base\Models\User;

/**
 * @SWG\Definition(
 *   definition="User",
 *   type="object",
 *   required={"email", "name"},
 *   @SWG\Property(
 *     property="email",
 *     type="string"
 *   ),
 *   @SWG\Property(
 *     property="name",
 *     type="string"
 *   ),
 *   @SWG\Property(
 *     property="firstName",
 *     type="string"
 *   ),
 *   @SWG\Property(
 *     property="lastName",
 *     type="string"
 *   ),
 *   @SWG\Property(
 *     property="roles",
 *     type="array"
 *   )
 * )
 */
class UserTransformer extends AbstractTransformer {

    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [
        'roles'
    ];

    /**
     * Transforms user entity
     *
     * @param User|array $user User entity
     *
     * @return array
     */
    public function transform($user)
    {
        $user = $this->entityToArray(User::class, $user);
        return [
            'id'        => (int) $user['id'],
            'email'     => $user['email'],
            'name'      => $user['name'],
            'firstName' => $user['first_name'],
            'lastName'  => $user['last_name'],
            'roles'     => !empty($user['roles']) ? $user['roles'] : []
        ];
    }

    /**
     * Include Roles
     *
     * @param User $user Translation
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeRoles(User $user)
    {
        $roles = $user->roles;
        return $this->collection($roles, new RoleTransformer());
    }
}
