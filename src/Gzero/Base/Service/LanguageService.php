<?php namespace Gzero\Base\Service;

use Gzero\Base\Model\Language;
use Illuminate\Cache\CacheManager;
use Illuminate\Cache\Repository;
use Illuminate\Database\Eloquent\Collection;

class LanguageService {

    /**
     * All languages
     *
     * @var Collection
     */
    private $languages;

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
     * Refresh $languages cache
     *
     * @return void
     */
    public function refresh()
    {
        if ($this->cache->has('$languages')) {
            $this->cache->forget('$languages');
            $this->init();
        }
    }

    /**
     * Get lang by lang code
     *
     * @param string $code Lang code eg. "en"
     *
     * @throws RepositoryException
     * @return \Gzero\Base\Model\Language
     */
    public function getByCode($code)
    {
        return $this->languages->filter(
            function ($language) use ($code) {
                return $language->code == $code;
            }
        )->first();
    }

    /**
     * Get current language
     *
     * @return \Gzero\Base\Model\Language
     */
    public function getCurrent()
    {
        return $this->getByCode(app()->getLocale());
    }

    /**
     * Get all languages
     *
     * @return Collection
     */
    public function getAll()
    {
        return $this->languages;
    }

    /**
     * Get all enabled languages
     *
     * @return Collection
     */
    public function getAllEnabled()
    {
        return $this->languages->filter(
            function ($lang) {
                return ($lang->is_enabled) ? $lang : false;
            }
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function getDefault()
    {
        return $this->languages->first(function ($value) {
            return $value->is_default;
        });
    }

    /**
     * Init languages from database or cache
     *
     * @return void
     */
    protected function init()
    {
        if ($this->cache->get('$languages')) {
            $this->languages = $this->cache->get('$languages');
        } else {
            $this->languages = Language::all();
            $this->cache->forever('$languages', $this->languages);
        }
    }
}
