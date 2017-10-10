<?php namespace Gzero\Core;

use Gzero\Repository\OptionService;

class OptionsService {

    /**
     * @var OptionService
     */
    protected $repository;

    /**
     * OptionsService constructor.
     *
     * @param OptionService $repo options repository
     */
    public function __construct(OptionService $repo)
    {
        $this->repository = $repo;
    }

    /**
     * Return list of all options categories
     *
     * @return array of categories
     */
    public function getCategories()
    {
        return $this->repository->getCategories();
    }

    /**
     * Return all options from given category
     *
     * @param string $categoryKey category key
     *
     * @return array of options
     */
    public function getOptions($categoryKey)
    {
        return $this->repository->getOptions($categoryKey);
    }

    /**
     * Return a single option
     *
     * @param string $categoryKey category key
     * @param string $optionKey   option key
     *
     * @return string option value
     */
    public function getOption($categoryKey, $optionKey)
    {
        return $this->repository->getOption($categoryKey, $optionKey);
    }

}
