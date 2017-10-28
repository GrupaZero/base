<?php namespace Gzero\Base\Repositories;

use Gzero\Base\Models\User;

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

    public function getMany()
    {
        // TODO: Implement getMany() method.
    }
}
