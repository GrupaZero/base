<?php namespace Gzero\Base\Repositories;

use Gzero\Base\Models\Route;
use Gzero\Base\QueryBuilder;
use Gzero\Base\Services\RepositoryException;

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
     * @param QueryBuilder $builder Query builder
     * @param int          $page    Page number
     *
     * @throws RepositoryException
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMany(QueryBuilder $builder, int $page = 1)
    {
        $query = Route::query();

        if ($builder->hasRelation('translations')) {
            if (!$builder->getRelationFilter('translations', 'language_code')) {
                throw new RepositoryException('Language code is required');
            }
            $query->join('route_translations as t', 'routes.id', '=', 't.route_id');
            $builder->applyRelationFilters('translations', 't', $query);
            $builder->applyRelationSorts('translations', 't', $query);
        }

        $builder->applyFilters($query);
        $builder->applySorts($query);

        /** @TODO Pagination */

        return $query->offset($builder->getPageSize() * ($page - 1))
            ->limit($builder->getPageSize())
            ->get(['routes.*']);
    }
}
