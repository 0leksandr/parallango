<?php

namespace tests\integration\AppBundle;

use PHPUnit_Framework_TestCase;
use Utils\ServiceContainer;

class NrBooksTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function allActiveAuthorsShouldHaveNrBooks()
    {
        $serviceContainer = ServiceContainer::get('test');
        $languagePairs = $serviceContainer->get('language')->getPairs();
        $this->assertEquals(1, count($languagePairs));
        $authors = $serviceContainer->get('author')->getAll();
        foreach ($authors as $author) {
            $this->assertGreaterThan(0, $author->getNrBooks());
        }
    }
}
