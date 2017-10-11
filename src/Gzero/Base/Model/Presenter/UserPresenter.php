<?php namespace Gzero\Base\Model\Presenter;

class UserPresenter extends BasePresenter {

    /**
     * Get display name nick or first and last name
     *
     * @return string
     */
    public function displayName()
    {
        if ($this->name && config('gzero.use_users_nicks')) {
            return $this->name;
        }

        if ($this->firstName || $this->lastName) {
            return $this->firstName . ' ' . $this->lastName;
        }
        return trans('common.anonymous');
    }

}
