<?php namespace Gzero\Base\Model;

class GuestUser extends User {

    /**
     * @return boolean
     */
    public function isSuperAdmin()
    {
        return false;
    }

    /**
     * @return boolean
     */
    public function isGuest()
    {
        return true;
    }

}
