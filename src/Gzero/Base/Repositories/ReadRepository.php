<?php namespace Gzero\Base\Repositories;

use Gzero\Base\Query\QueryBuilder;

interface ReadRepository {

    /**
     * @param mixed $id Entity id
     *
     * @return mixed
     */
    public function getById($id);

    /**
     * @param QueryBuilder $builder Query builder
     *
     * @return mixed
     */
    public function getMany(QueryBuilder $builder);
}
