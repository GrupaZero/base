<?php namespace Gzero\Base\Services;

use Gzero\Base\Models\Option;
use Gzero\Base\Models\OptionCategory;
use Gzero\Base\Repositories\RepositoryException;
use Gzero\Base\Repositories\RepositoryValidationException;
use Illuminate\Cache\CacheManager;
use Illuminate\Cache\Repository;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class OptionService {

    /**
     * @var OptionCategory
     */
    protected $optionCategoryModel;

    /**
     * @var Option
     */
    protected $optionModel;

    /**
     * @var array Whole options hierarchy.
     *            This array maps each category name to an array (which may be empty)
     *            mapping param names to their values
     */
    private $options;

    /**
     * @var Repository
     */
    private $cache;

    /**
     * OptionRepository constructor
     *
     * @param OptionCategory $optionCategory OptionCategory model
     * @param Option         $option         Option model
     * @param CacheManager   $cache          Cache
     */
    public function __construct(OptionCategory $optionCategory, Option $option, CacheManager $cache)
    {
        $this->optionCategoryModel = $optionCategory;
        $this->optionModel         = $option;
        $this->cache               = $cache;
        $this->init();
    }

    /**
     * Refresh options cache
     *
     * @return boolean
     */
    public function refresh()
    {
        if ($this->cache->has('options')) {
            $this->cache->forget('options');
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get all option categories
     *
     * @return Collection collection with category names
     */
    public function getCategories()
    {
        return collect(array_keys($this->options));
    }

    /**
     * Get all options within the given category
     *
     * @param string $categoryKey category key
     *
     * @return Collection collection mapping option keys (within the given category) to their values
     * @throws RepositoryException When queried for non-existent category
     */
    public function getOptions($categoryKey)
    {
        $this->requireCategoryExists($categoryKey);

        return collect($this->options[$categoryKey]);
    }

    /**
     * Get the value of the given option.
     *
     * @param string $categoryKey Category key
     * @param string $optionKey   Option key
     *
     * @return string option value
     * @throws RepositoryException When queried for non-existent option
     */
    public function getOption($categoryKey, $optionKey)
    {
        $this->requireOptionExists($categoryKey, $optionKey);


        return $this->options[$categoryKey][$optionKey];
    }

    /**
     * Creates new option category
     *
     * @param string $categoryKey Category key
     *
     * @return void
     * @throws RepositoryException When queried for non-existent category
     */
    public function createCategory($categoryKey)
    {
        $this->requireCategoryDoesNotExist($categoryKey);

        $this->validateName($categoryKey);

        $this->optionCategoryModel->create(['key' => $categoryKey]);
        $this->refresh();
        $this->options[$categoryKey] = [];
    }

    /**
     * Create new option
     *
     * @param string $categoryKey Category key
     * @param string $optionKey   Option key
     * @param string $value       Option value
     *
     * @return void
     * @throws RepositoryException When queried for non-existent category
     */
    public function updateOrCreateOption($categoryKey, $optionKey, $value)
    {
        $this->validateName($optionKey);
        $this->validateValue($value);

        $this->requireCategoryExists($categoryKey);

        $this->optionModel->updateOrCreate(['category_key' => $categoryKey, 'key' => $optionKey], ['value' => $value]);
        $this->refresh();
        $this->options[$categoryKey][$optionKey] = $value;
    }

    /**
     * Delete the given category
     *
     * @param string $categoryKey Category key
     *
     * @return void
     * @throws RepositoryException When queried for non-existent category
     */
    public function deleteCategory($categoryKey)
    {
        $this->requireCategoryExists($categoryKey);

        $this->optionCategoryModel->destroy($categoryKey);
        $this->refresh();
        unset($this->options[$categoryKey]);
    }

    /**
     * Remove the given option
     *
     * @param string $categoryKey Category key
     * @param string $optionKey   Option key
     *
     * @return void
     * @throws RepositoryException When queried for non-existent category or option
     */
    public function deleteOption($categoryKey, $optionKey)
    {
        $this->requireOptionExists($categoryKey, $optionKey);

        $this->optionModel->where(['category_key' => $categoryKey, 'key' => $optionKey])->delete();
        $this->refresh();
        unset($this->options[$categoryKey][$optionKey]);
    }

    /**
     * Init options from database or cache
     *
     * @return void
     */
    protected function init()
    {
        if ($this->cache->get('options')) {
            $this->options = $this->cache->get('options');
        } else {
            $this->extractCategoriesFromModel($this->optionCategoryModel->newQuery()->get(['key'])->sortBy('key'));
            $this->extractOptionsFromModel(
                $this->optionModel->newQuery()->get(['id', 'category_key', 'key', 'value'])->sortBy('id')
            );
            $this->cache->forever('options', $this->options);
        }
    }

    /**
     * Extract the actual data from the Eloquent models into simple arrays
     *
     * @param EloquentCollection $optionCategoryModels Eloquent models
     *
     * @return void
     */
    private function extractCategoriesFromModel($optionCategoryModels)
    {
        $this->options = [];
        $optionCategoryModels->pluck('key')->each(
            function ($categoryKey) {
                $this->options[$categoryKey] = [];
            }
        );
    }

    /**
     * Extract the actual data from the Eloquent models into simple arrays
     *
     * @param EloquentCollection $optionModels Eloquent models
     *
     * @return void
     */
    private function extractOptionsFromModel($optionModels)
    {
        $optionModels->each(
            function ($optionModel) {
                $this->options[$optionModel->category_key][$optionModel->key] = $optionModel->value;
            }
        );
    }

    /**
     * Validate the string for name of category or option
     *
     * @param string $name Name to validate
     *
     * @return void
     * @throws RepositoryValidationException
     */
    private function validateName($name)
    {
        if (!is_string($name) || trim($name) === '') {
            throw new RepositoryValidationException('Invalid category name format');
        }
    }

    /**
     * Validate the array for value of an option
     *
     * @param string $name Value to validate
     *
     * @return void
     * @throws RepositoryValidationException
     */
    private function validateValue($name)
    {
        if (!is_array($name)) {
            throw new RepositoryValidationException('Invalid option value format');
        }
    }

    /**
     * Check if the given option exists
     *
     * @param string $categoryKey Category key
     * @param string $optionKey   Option key
     *
     * @return bool
     * @throws RepositoryException When queried for non-existent category
     */
    private function optionExists($categoryKey, $optionKey)
    {
        $this->requireCategoryExists($categoryKey);

        return array_key_exists($optionKey, $this->options[$categoryKey]);
    }

    /**
     * Make sure the given option exists - raise exception if not
     *
     * @param string $categoryKey Category key
     * @param string $optionKey   Option key
     *
     * @return void
     * @throws RepositoryValidationException
     */
    private function requireOptionExists($categoryKey, $optionKey)
    {
        if (!$this->optionExists($categoryKey, $optionKey)) {
            throw new RepositoryValidationException(
                'Option ' . $optionKey . ' in category ' . $categoryKey . ' does not exist'
            );
        }
    }

    /**
     * Check if the category exists
     *
     * @param string $categoryKey Category key
     *
     * @return bool
     */
    private function categoryExists($categoryKey)
    {
        return array_key_exists($categoryKey, $this->options);
    }

    /**
     * Make sure the given category exists - raise exception if not
     *
     * @param string $categoryKey Category key
     *
     * @return void
     * @throws RepositoryValidationException
     */
    private function requireCategoryExists($categoryKey)
    {
        if (!$this->categoryExists($categoryKey)) {
            throw new RepositoryValidationException('Category ' . $categoryKey . ' does not exist');
        }
    }

    /**
     * Make sure the given category does not already exist - raise exception if so
     *
     * @param string $categoryKey Category key
     *
     * @return void
     * @throws RepositoryValidationException
     */
    private function requireCategoryDoesNotExist($categoryKey)
    {
        if ($this->categoryExists($categoryKey)) {
            throw new RepositoryValidationException('The category ' . $categoryKey . ' already exists');
        }
    }
}
