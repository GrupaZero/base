<?php namespace Gzero\Base\Transformers;

use Gzero\Base\Models\Role;

class RoleTransformer extends AbstractTransformer {

    /**
     * Transforms role entity
     *
     * @param Role|array $role Role entity
     *
     * @return array
     */
    public function transform($role)
    {
        $role = $this->entityToArray(Role::class, $role);
        return [
            'id'        => (int) $role['id'],
            'name'      => $role['name'],
            'createdAt' => $role['created_at'],
            'updatedAt' => $role['updated_at']
        ];
    }
}
