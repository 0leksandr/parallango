<?php

namespace AppBundle\Entity\Page;

use PHPUnit_Framework_TestCase;
use Utils\ServiceContainer;

class PageRepositoryTest extends PHPUnit_Framework_TestCase
{
    /** @var PageRepository */
    private $SUT;

    public function setUp()
    {
        $this->SUT = ServiceContainer::get('test')->get('page');
    }

    /**
     * @test
     */
    public function testBeginsAndEnds()
    {
        $this->assertTrue(false);
    }
}
