<?php namespace Gzero\Base\Services;

use Gzero\Base\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Events\Dispatcher;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class UserService extends BaseService implements AuthenticatableContract {

    /**
     * @var User
     */
    protected $model;

    /**
     * The events dispatcher
     *
     * @var Dispatcher
     */
    protected $events;

    /**
     * User repository constructor
     *
     * @param User       $user   Content model
     * @param Dispatcher $events Events dispatcher
     */
    public function __construct(User $user, Dispatcher $events)
    {
        $this->model  = $user;
        $this->events = $events;
    }

    // @codingStandardsIgnoreStart

    /**
     * Eager load relations for eloquent collection
     *
     * @param Collection $results Eloquent collection
     *
     * @return void
     */
    protected function listEagerLoad($results)
    {
        $results->load('roles');
    }

    /**
     * Get all users with specific criteria
     *
     * @param array    $criteria Filter criteria
     * @param array    $orderBy  Array of columns
     * @param int|null $page     Page number (if null == disabled pagination)
     * @param int|null $pageSize Limit results
     *
     * @throws RepositoryException
     * @return Collection
     */
    public function getUsers(array $criteria = [], array $orderBy = [], $page = 1, $pageSize = self::ITEMS_PER_PAGE)
    {
        $query  = $this->newORMQuery();
        $parsed = $this->parseArgs($criteria, $orderBy);
        $this->handleFilterCriteria($this->getTableName(), $query, $parsed['filter']);
        $this->handleOrderBy(
            $this->getTableName(),
            $parsed['orderBy'],
            $query,
            $this->userDefaultOrderBy()
        );
        return $this->handlePagination($this->getTableName(), $query, $page, $pageSize);
    }

    /**
     * Default order for user query
     *
     * @return callable
     */
    protected function userDefaultOrderBy()
    {
        return function ($query) {
            $query->orderBy('id', 'DESC');
        };
    }

    /*
    |--------------------------------------------------------------------------
    | START AuthenticatableContract
    |--------------------------------------------------------------------------
    */

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return $this->model->getKeyName();
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->model->getKey();
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->model->password;
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken()
    {
        return $this->model->{$this->getRememberTokenName()};
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string $value
     *
     * @return void
     */
    public function setRememberToken($value)
    {
        $this->model->{$this->getRememberTokenName()} = $value;
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    /*
    |--------------------------------------------------------------------------
    | END AuthenticatableContract AND CanResetPasswordContract
    |--------------------------------------------------------------------------
    */
}
