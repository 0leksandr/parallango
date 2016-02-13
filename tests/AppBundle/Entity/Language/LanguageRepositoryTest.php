<?php

namespace AppBundle\Entity\Language;

use Utils\ServiceContainer;

require_once __DIR__ . '/../../../../src/Utils/Utils.php';

class LanguageRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var LanguageRepository */
    private $SUT;

    public function setUp()
    {
        $this->SUT = ServiceContainer::get('test')->get('language');
    }

    /**
     * @test
     */
    public function allLanguagesShouldHaveUniqueCode()
    {
        $languages = $this->SUT->getAll();
        $this->assertGreaterThan(0, count($languages));
        $this->assertEquals(
            count($languages),
            count(array_unique(mpull($languages, 'getCode')))
        );
    }

    /**
     * @test
     */
    public function thereShouldBeActiveLanguages()
    {
        $nrActiveLanguages = array_sum(array_map(
            function (Language $language) {
                return $language->isActive();
            },
            $this->SUT->getAll()
        ));
        $this->assertGreaterThan(0, $nrActiveLanguages);
        $this->assertEquals($nrActiveLanguages, count($this->SUT->getActive()));
    }
}
