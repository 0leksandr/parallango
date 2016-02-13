<?php

namespace AppBundle\Entity\Section;

use Utils\ServiceContainer;

class SectionRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var SectionRepository */
    private $SUT;

    public function setUp()
    {
        $this->SUT = ServiceContainer::get('test')->get('section');
    }

    /**
     * @test
     */
    public function allSectionsShouldHaveTitleInActiveLanguages()
    {
        $languages =
            ServiceContainer::get('test')->get('language')->getActive();
        foreach ($this->SUT->getAll() as $section) {
            foreach ($languages as $language) {
                $title = $section->getTitle($language);
                $this->assertNotEmpty($title);
            }
        }
    }
}
