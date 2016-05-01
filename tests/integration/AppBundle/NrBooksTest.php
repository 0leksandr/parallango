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
        // TODO: use repositories
        $sql = ServiceContainer::get('test')->get('sql');
        $this->assertTrue(false);
    }
}
