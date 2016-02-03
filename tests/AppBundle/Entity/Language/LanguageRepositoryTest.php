<?php

namespace AppBundle\Entity\Language;

class LanguageRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var LanguageRepository */
    private $SUT;

    public function setUp()
    {
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
            array_unique(mpull($languages, 'getCode'))
        );
    }
}
