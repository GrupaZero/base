<?php namespace App;

use Gzero\Base\Models\Language;
use Gzero\Base\Models\Routable;
use Gzero\Base\Models\Route;
use Illuminate\Http\Response;

class HelloWorld implements Routable {
    public function handle(Route $route, Language $lang): Response
    {
        return response('Hello World');
    }
}