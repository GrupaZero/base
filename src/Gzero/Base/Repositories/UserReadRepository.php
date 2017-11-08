<?php namespace Gzero\Base\Repositories;

use Gzero\Base\Models\User;
use Gzero\Base\QueryBuilder;

class UserReadRepository implements ReadRepository {

    /**
     * @param int $id Entity id
     *
     * @return mixed
     */
    public function getById($id)
    {
        return User::find($id);
    }

    /**
     * Retrieve a user by given email
     *
     * @param  string $email User email
     *
     * @return User|mixed
     */
    public function getByEmail($email)
    {
        return User::query()->where('email', '=', $email)->first();
    }

    /**
     * @param QueryBuilder $builder Query builder
     * @param int          $page    Page number
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getMany(QueryBuilder $builder, int $page = 1)
    {
        $query = User::query();

        $builder->applyFilters($query);
        $builder->applySorts($query);

        return $query->offset($builder->getPageSize() * ($page - 1))
            ->limit($builder->getPageSize())
            ->get(['users.*']);
    }
}
