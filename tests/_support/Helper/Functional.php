<?php namespace Base\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Gzero\Base\Model\User;

class Functional extends \Codeception\Module {

    /**
     * Create user and return entity
     *
     * @param array $attributes
     *
     * @return User
     */
    public function haveUser($attributes = [])
    {
        return factory(User::class)->create($attributes);
    }

}
