<?php namespace Gzero\Base\Repositories;

use Gzero\Base\QueryBuilder;

interface ReadRepository {

    /**
     * @param mixed $id Entity id
     *
     * @return mixed
     */
    public function getById($id);

    /**
     * @param QueryBuilder $builder Query builder
     * @param int          $page    Page number
     *
     * @return mixed
     */
    public function getMany(QueryBuilder $builder, int $page = 1);
}
