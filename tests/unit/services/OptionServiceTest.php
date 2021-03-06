<?php namespace Base;

use Gzero\Base\Exception;
use Gzero\Base\Models\Language;
use Gzero\Base\Models\Option;
use Gzero\Base\Models\OptionCategory;
use Gzero\Base\Repositories\RepositoryValidationException;
use Gzero\Base\Services\OptionService;

class OptionServiceTest extends \Codeception\Test\Unit {

    /** @var UnitTester */
    protected $tester;

    /** @var OptionService */
    protected $service;

    protected $expectedOptions;

    protected function _before()
    {
        $this->recreateRepository();
        $this->setExpectedOptions();
    }

    /** @test */
    public function itChecksExistenceOfCategoryWhenGettingAnOption()
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

    /** @test */
    public function ItChecksExistenceOfCategoryAndOptionWhenGettingAnNonExistingOption()
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

    /** @test */
    public function ItChecksExistenceOfOptionWhenGettingAnOptionFromExistingCategory()
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

    /** @test */
    public function ItChecksExistenceOfCategoryWhenDeletingAnOption()
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

    /** @test */
    public function ItChecksExistenceOfCategoryAndOptionWhenDeletingAnNonExistingOption()
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

    /** @test */
    public function ItChecksExistenceOfOptionWhenDeletingAnOption()
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

    /** @test */
    public function ItGetsOptionFromGeneralCategory()
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

    /** @test */
    public function ItGetsAllOptionsFromGeneralCategory()
    {
        $categoryKey = 'general';

        $this->assertEquals(
            collect($this->expectedOptions[$categoryKey]),
            $this->service->getOptions($categoryKey)
        );

        $this->assertNotNull(OptionCategory::getByKey($categoryKey));
    }

    /** @test */
    public function CanCreateCategory()
    {
        $categoryKey = 'New category';

        $this->service->createCategory($categoryKey);

        $this->assertNotNull(OptionCategory::getByKey($categoryKey));
    }

    /** @test */
    public function CanCreateOption()
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


    /** @test */
    public function CanDeleteCategory()
    {
        $categoryKey = 'general';

        $this->service->deleteCategory($categoryKey);
        $this->assertNull(OptionCategory::getByKey($categoryKey));
    }

    /** @test */
    public function canDeleteOption()
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
                foreach (Language::all()->toArray() as $lang) {
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
