<?php namespace Gzero\Base\Repositories;

use Gzero\Base\Models\Route;

class RouteReadRepository implements ReadRepository {

    /**
     * @param int $id Entity id
     *
     * @return mixed
     */
    public function getById($id)
    {
        return Route::find($id);
    }

    /**
     * @param string $path         URI path
     * @param string $languageCode Language code
     * @param bool   $onlyActive   Trigger
     *
     * @return Route|mixed
     */
    public function getByPath(string $path, string $languageCode, bool $onlyActive = false)
    {
        return Route::query()
            ->join('route_translations', function ($join) use ($languageCode, $path, $onlyActive) {
                $join->on('routes.id', 'route_translations.route_id')
                    ->where('language_code', $languageCode)
                    ->where('path', $path);
                if ($onlyActive) {
                    $join->where('is_active', true);
                }
            })
            ->first();
    }


    /**
     * @return mixed
     */
    public function getMany()
    {
        // TODO: Implement getMany() method.
    }
}
