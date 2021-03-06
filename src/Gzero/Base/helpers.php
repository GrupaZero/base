<?php

use Gzero\Base\Services\LanguageService;
use Illuminate\Support\Facades\Route;

if (!function_exists('setMultiLanguageRouting')) {

    /**
     * Returns routing array with lang prefix
     *
     * @param array $routingOptions Additional routing options
     *
     * @return array
     */
    function setMultiLanguageRouting(array $routingOptions = [])
    {
        if (config('gzero.ml')) {
            return array_merge(
                $routingOptions,
                ['domain' => config('gzero.domain'), 'prefix' => app()->getLocale()]
            );
        } else {
            // Set domain for static pages block finder
            return array_merge(
                $routingOptions,
                ['domain' => config('gzero.domain')]
            );
        }
    }
}

if (!function_exists('addMultiLanguageRoutes')) {

    /**
     * It registers new multi language routes
     *
     * @param Closure $closure Closure with route definitions
     *
     * @return void
     */
    function addMultiLanguageRoutes(Closure $closure)
    {
        /** @var LanguageService $service */
        $service   = resolve(LanguageService::class);
        $languages = $service->getAllEnabled();
        foreach ($languages as $language) {
            $prefix = '';
            if (!$language->is_default) {
                $prefix = $language->code;
            }
            Route::prefix($prefix)
                ->middleware('web')
                ->group(function () use ($closure, $language) {
                    $closure(resolve('router'), $language->code);
                });
        }
    }
}

if (!function_exists('mlSuffix')) {

    /**
     * It adds language suffix
     *
     * @param string $name     Route name
     * @param string $language Language code
     *
     * @return string
     */
    function mlSuffix($name, $language = null)
    {
        $language = $language ?: app()->getLocale();
        return $name . '-' . $language;
    }
}

if (!function_exists('routeMl')) {

    /**
     * Generate the URL to a named multi language route.
     *
     * @param string $name       Route name
     * @param string $language   Language code
     * @param array  $parameters parameters
     * @param bool   $absolute   Absolute trigger
     *
     * @return string
     */
    function routeMl($name, $language = null, $parameters = [], $absolute = true)
    {
        return \route(mlSuffix($name, $language), $parameters, $absolute);
    }
}

if (!function_exists('apiUrl')) {
    /**
     * Generate a url for the api
     *
     * @param  string $path       Url path
     * @param  mixed  $parameters Additional parameters
     * @param  bool   $secure     Secure trigger
     *
     * @return \Illuminate\Contracts\Routing\UrlGenerator|string
     */
    function apiUrl($path = null, $parameters = [], $secure = null)
    {
        $url = url("/v1" . str_start($path, '/'), $parameters, $secure);
        return preg_match('|^http.?://api\.|', $url) ? $url : str_replace('://', '://api.', $url);
    }
}

if (!function_exists('isProviderLoaded')) {
    /**
     * Check if specified provider is loaded
     *
     * @param string $provider name
     *
     * @return boolean
     */
    function isProviderLoaded($provider)
    {
        $loadedProviders = app()->getLoadedProviders();
        return isset($loadedProviders[$provider]);
    }
}

if (!function_exists('croppaUrl')) {
    /**
     * Pass through URL requests to URL->generate().
     *
     * @param string  $url     URL of an image that should be cropped
     * @param integer $width   Target width
     * @param integer $height  Target height
     * @param array   $options Additional Croppa options, passed as key/value pairs.  Like array('resize')
     *
     * @return string The new path to your thumbnail
     * @see Bkwld\Croppa\URL::generate()
     */
    function croppaUrl($url, $width = null, $height = null, $options = null)
    {
        if ($width === null && $height === null) {
            $width  = config('gzero.image.max_width');
            $height = config('gzero.image.max_height');
        }
        return app('Bkwld\Croppa\Helpers')->url($url, $width, $height, $options);
    }
}

if (!function_exists('croppaReset')) {
    /**
     * Delete just the crops, leave the source image
     *
     * @param string $url URL of src image
     *
     * @return void
     * @see Bkwld\Croppa\Storage::deleteCrops()
     */
    function croppaReset($url)
    {
        app('Bkwld\Croppa\Helpers')->reset($url);
    }
}

if (!function_exists('option')) {
    /**
     * Return single option
     *
     * @param string         $categoryKey category key
     * @param string         $optionKey   option key
     * @param boolean|string $fallback    fallback value
     * @param boolean|string $language    lang code
     *
     * @return array|false
     */
    function option($categoryKey, $optionKey, $fallback = false, $language = false)
    {
        $option   = app('options')->getOption($categoryKey, $optionKey);
        $language = $language ? $language : app()->getLocale();

        if (array_key_exists($language, $option)) {
            return $option[$language];
        } else {
            return $fallback ? $fallback : false;
        }
    }
}

// Snake Case to match PHP & Laravel array functions

if (!function_exists('array_snake_case_keys')) {
    /**
     * It creates new array from given array with snake_case keys
     *
     * @param array $array Array to get keys
     *
     * @return array
     */
    function array_snake_case_keys(array $array)
    {
        $results = [];
        foreach ($array as $key => $val) {
            $newKey = snake_case($key);
            if (!is_array($val)) {
                $results[$newKey] = $val;
            } else {
                $results[$newKey] = array_snake_case_keys($val);
            }
        }
        return $results;
    }
}

if (!function_exists('array_camel_case_keys')) {
    /**
     * It creates new array from given array with camel_case keys
     *
     * @param array $array Array to get keys
     *
     * @return array
     */
    function array_camel_case_keys(array $array)
    {
        $results = [];
        foreach ($array as $key => $val) {
            $newKey = camel_case($key);
            if (!is_array($val)) {
                $results[$newKey] = $val;
            } else {
                $results[$newKey] = array_camel_case_keys($val);
            }
        }
        return $results;
    }
}
