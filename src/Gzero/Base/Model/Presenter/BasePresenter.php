<?php namespace Gzero\Base\Model\Presenter;

use Robbo\Presenter\Presenter;

class BasePresenter extends Presenter {

    /**
     * Pass any unknown variable calls to present{$variable} or fall through to the injected object.
     *
     * @param string $var Variable name
     *
     * @return mixed
     */
    public function __get($var)
    {
        return parent::__get(snake_case($var));
    }

    /**
     * Allow ability to run isset() on a variable
     *
     * @param string $name Variable name
     *
     * @return boolean
     */
    public function __isset($name)
    {
        return parent::__isset(snake_case($name));
    }

    /**
     * Allow to unset a variable through the presenter
     *
     * @param string $name Variable name
     *
     * @return void
     */
    public function __unset($name)
    {
        parent::__unset(snake_case($name));
    }

}
