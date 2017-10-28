<?php namespace Gzero\Base\Services;

interface QueryService {

    /**
     * @param mixed $id Entity id
     *
     * @return mixed
     */
    public function getById($id);

}
