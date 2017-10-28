<?php namespace Gzero\Base\Repositories;

interface ReadRepository {

    /**
     * @param mixed $id Entity id
     *
     * @return mixed
     */
    public function getById($id);

    public function getMany();
}
