<?php namespace unit;

use Gzero\Base\Exception;
use Gzero\Base\Model\Lang;
use Gzero\Base\Model\Option;
use Gzero\Base\Model\OptionCategory;
use Gzero\Base\Service\OptionService;
use Base\UnitTester;
use Gzero\Base\Service\RepositoryValidationException;

class OptionServiceTest extends \Codeception\Test\Unit {

    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var OptionService
     */
    protected $service;

    protected $expectedOptions;

    protected function _before()
    {
        $this->recreateRepository();
        $this->setExpectedOptions();
    }

    /**
     * @test
     */
    public function it_checks_existence_of_category_when_getting_an_option()
    {
        $categoryKey = 'nonexistent category';

        $this->assertNull(OptionCategory::getByKey($categoryKey));

        try {
            $this->service->getOptions($categoryKey);
        } catch (Exception $exception) {
            $this->assertEquals(RepositoryValidationException::class, get_class($exception));
            $this->assertEquals('Category nonexistent category does not exist', $exception->getMessage());
            return;
        }

        $this->fail('Exception should be thrown');
    }

    /**
     * @test
     */
    public function it_checks_existence_of_category_and_option_when_getting_an_non_existing_option()
    {
        $optionKey   = 'nonexistent option';
        $categoryKey = 'nonexistent category';

        $this->assertNull(Option::getByKey($optionKey));
        $this->assertNull(OptionCategory::getByKey($categoryKey));

        try {
            $this->service->getOption($categoryKey, $optionKey);
        } catch (Exception $exception) {
            $this->assertEquals(RepositoryValidationException::class, get_class($exception));
            $this->assertEquals('Category nonexistent category does not exist', $exception->getMessage());
            return;
        }

        $this->fail('Exception should be thrown');
    }

    /**
     * @test
     */
    public function it_checks_existence_of_option_when_getting_an_option_from_existing_category()
    {
        $optionKey   = 'nonexistent option';
        $categoryKey = 'general';

        $this->assertNull(Option::getByKey($optionKey));
        $this->assertNotNull(OptionCategory::getByKey($categoryKey));

        try {
            $this->service->getOption($categoryKey, $optionKey);
        } catch (Exception $exception) {
            $this->assertEquals(RepositoryValidationException::class, get_class($exception));
            $this->assertEquals('Option nonexistent option in category general does not exist', $exception->getMessage());
            return;
        }

        $this->fail('Exception should be thrown');
    }

    /**
     * @test
     */
    public function it_checks_existence_of_category_when_deleting_an_option()
    {
        $categoryKey = 'nonexistent category';

        $this->assertNull(OptionCategory::getByKey($categoryKey));

        try {
            $this->service->deleteCategory($categoryKey);
        } catch (Exception $exception) {
            $this->assertEquals(RepositoryValidationException::class, get_class($exception));
            $this->assertEquals('Category nonexistent category does not exist', $exception->getMessage());
            return;
        }

        $this->fail('Exception should be thrown');
    }

    /**
     * @test
     */
    public function it_checks_existence_of_category_and_option_when_deleting_an_non_existing_option()
    {
        $optionKey   = 'nonexistent option';
        $categoryKey = 'nonexistent category';

        $this->assertNull(Option::getByKey($optionKey));
        $this->assertNull(OptionCategory::getByKey($categoryKey));

        try {
            $this->service->deleteOption($categoryKey, $optionKey);
        } catch (Exception $exception) {
            $this->assertEquals(RepositoryValidationException::class, get_class($exception));
            $this->assertEquals('Category nonexistent category does not exist', $exception->getMessage());
            return;
        }

        $this->fail('Exception should be thrown');
    }

    /**
     * @test
     */
    public function it_checks_existence_of_option_when_deleting_an_option()
    {
        $optionKey   = 'nonexistent option';
        $categoryKey = 'general';

        $this->assertNull(Option::getByKey($optionKey));
        $this->assertNotNull(OptionCategory::getByKey($categoryKey));

        try {
            $this->service->deleteOption($categoryKey, $optionKey);
        } catch (Exception $exception) {
            $this->assertEquals(RepositoryValidationException::class, get_class($exception));
            $this->assertEquals('Option nonexistent option in category general does not exist', $exception->getMessage());
            return;
        }

        $this->fail('Exception should be thrown');
    }

    /**
     * @test
     */
    public function it_gets_option_from_general_category()
    {
        $optionKey   = 'site_name';
        $categoryKey = 'general';

        $this->assertEquals(
            $this->expectedOptions[$categoryKey][$optionKey]['en'],
            $this->service->getOption($categoryKey, $optionKey)['en']
        );

        $this->assertNotNull(Option::getByKey($optionKey));
        $this->assertNotNull(OptionCategory::getByKey($categoryKey));
    }

    /**
     * @test
     */
    public function it_gets_all_options_from_general_category()
    {
        $categoryKey = 'general';

        $this->assertEquals(
            $this->expectedOptions[$categoryKey],
            $this->service->getOptions($categoryKey)
        );

        $this->assertNotNull(OptionCategory::getByKey($categoryKey));
    }

    /**
     * @test
     */
    public function can_create_category()
    {
        $categoryKey = 'New category';

        $this->service->createCategory($categoryKey);

        $this->assertNotNull(OptionCategory::getByKey($categoryKey));
    }

    /**
     * @test
     */
    public function can_create_option()
    {
        $categoryKey = 'general';
        $optionKey   = 'some option';
        $value       = ['en' => 'new option value'];

        $this->service->updateOrCreateOption($categoryKey, $optionKey, $value);

        $savedOption = OptionCategory::getByKey($categoryKey)->options()->where(['key' => $optionKey])->first();
        $this->assertNotNull($savedOption);
        $this->assertEquals($value, $savedOption->value);

        $this->recreateRepository();
        $this->assertEquals($value, $this->service->getOption($categoryKey, $optionKey));

        $this->assertNotNull(Option::getByKey($optionKey));
        $this->assertNotNull(OptionCategory::getByKey($categoryKey));
    }


    /**
     * @test
     */
    public function can_delete_category()
    {
        $categoryKey = 'general';

        $this->service->deleteCategory($categoryKey);
        $this->assertNull(OptionCategory::getByKey($categoryKey));
    }

    /**
     * @test
     */
    public function can_delete_option()
    {
        $categoryKey = 'general';
        $optionKey   = 'site_name';

        $this->service->deleteOption('general', $optionKey);
        $this->assertFalse(
            OptionCategory::getByKey($categoryKey)->options()->
            where(['key' => $optionKey])->exists()
        );

        $this->assertNull(Option::getByKey($optionKey));
    }

    private function recreateRepository()
    {
        $this->service = new OptionService(
            new OptionCategory(),
            new Option(),
            new \Illuminate\Cache\CacheManager($this->tester->getApplication())
        );
    }

    private function setExpectedOptions()
    {
        $this->expectedOptions = [
            'general' => [
                'site_name'          => [],
                'site_desc'          => [],
                'default_page_size'  => [],
                'cookies_policy_url' => [],
            ],
            'seo'     => [
                'seoDescLength'     => [],
                'googleAnalyticsId' => [],
            ]
        ];

        // Propagate Lang options based on gzero config
        foreach ($this->expectedOptions as $categoryKey => $category) {
            foreach ($this->expectedOptions[$categoryKey] as $key => $option) {
                foreach (Lang::all()->toArray() as $lang) {
                    if ($categoryKey != 'general') {
                        $this->expectedOptions[$categoryKey][$key][$lang['code']] = config('gzero.' . $categoryKey . '.' . $key);
                    } else {
                        $value = $this->getDefaultValueForGeneral($key);

                        $this->expectedOptions[$categoryKey][$key][$lang['code']] = $value;
                    }
                }
            }
        }
    }

    /**
     * It generates default value for general options
     *
     * @param $key
     *
     * @return mixed|string
     */
    private static function getDefaultValueForGeneral($key)
    {
        switch ($key) {
            case 'site_name':
                $value = "GZERO-CMS"; // Hardcoded from default migration
                break;
            case 'site_desc':
                $value = "GZERO-CMS Content management system.";
                break;
            default:
                $value = config('gzero.' . $key);
                return $value;
        }
        return $value;
    }
}
