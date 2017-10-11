<?php namespace Gzero\Repository;

use Gzero\Base\Model\Lang;
use Illuminate\Cache\CacheManager;
use Illuminate\Cache\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\App;

class LangService {

    /**
     * All languages
     *
     * @var Collection
     */
    private $langs;

    /**
     * @var Repository
     */
    private $cache;

    /**
     * LangRepository constructor
     *
     * @param CacheManager $cache Cache
     */
    public function __construct(CacheManager $cache)
    {
        $this->cache = $cache;
        $this->init();
    }

    /**
     * Refresh langs cache
     *
     * @return boolean
     */
    public function refresh()
    {
        if ($this->cache->has('langs')) {
            $this->cache->forget('langs');
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get lang by lang code
     *
     * @param string $code Lang code eg. "en"
     *
     * @throws RepositoryException
     * @return \Gzero\Base\Model\Lang
     */
    public function getByCode($code)
    {
        return $this->langs->filter(
            function ($lang) use ($code) {
                return $lang->code == $code;
            }
        )->first();
    }

    /**
     * Get current language
     *
     * @return \Gzero\Entity\Lang
     */
    public function getCurrent()
    {
        return $this->getByCode(App::getLocale());
    }

    /**
     * Get all languages
     *
     * @return Collection
     */
    public function getAll()
    {
        return $this->langs;
    }

    /**
     * Get all enabled langs
     *
     * @return Collection
     */
    public function getAllEnabled()
    {
        return $this->langs->filter(
            function ($lang) {
                return ($lang->is_enabled) ? $lang : false;
            }
        );
    }

    /**
     * Init languages from database or cache
     *
     * @return void
     */
    protected function init()
    {
        if ($this->cache->get('langs')) {
            $this->langs = $this->cache->get('langs');
        } else {
            /* @var QueryBuilder $qb */
            $this->langs = Lang::all();
            $this->cache->forever('langs', $this->langs);
        }
    }
}
