<?php namespace Gzero\Base\Http\Controller;

use Illuminate\Routing\Controller as LaravelController;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends LaravelController {

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

}
