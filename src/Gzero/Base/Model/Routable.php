<?php namespace Gzero\Base\Model;

use Illuminate\Http\Response;

interface Routable {

    /**
     * @param Route    $route Route
     * @param Language $lang  Language
     *
     * @return Response
     */
    public function handle(Route $route, Language $lang): Response;

}
