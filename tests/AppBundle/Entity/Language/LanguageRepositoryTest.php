<?php

namespace AppBundle\Entity\Language;

use AppBundle\Utils\ServiceContainer;

require_once __DIR__ . '/../../../../src/AppBundle/Utils/Utils.php';

class LanguageRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var LanguageRepository */
    private $SUT;

    public function setUp()
    {
        $this->SUT = ServiceContainer::get()->get('language');
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
}
