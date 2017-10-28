<?php namespace Gzero\Base\Services;

use Gzero\Base\Models\User;

class UserQueryService implements QueryService {

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

}
