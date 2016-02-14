<?php

namespace AppBundle\Entity\Book;

use PHPUnit_Framework_TestCase;
use Utils\ServiceContainer;

class BookRepositoryTest extends PHPUnit_Framework_TestCase
{
    /** @var BookRepository */
    private $SUT;

    public function setUp()
    {
        $this->SUT = ServiceContainer::get('test')->get('book');
    }

    /**
     * @test
     */
    public function allBooksShouldHaveUniqueTitleAndAuthor()
    {
        $books = $this->SUT->getAll();
        $authorTitles = array_map(function (Book $book) {
            return sprintf(
                '%d ### %s',
                $book->getAuthor()->getId(),
                $book->getTitle()
            );
        }, $books);

        $repeats = array_count_values($authorTitles);
        $this->assertEquals(1, max($repeats), sprintf(
            'Repeated: %s',
            print_r(array_filter($repeats, function ($int) {
                return $int !== 1;
            }), true)
        ));

        $this->assertEquals(
            count($books),
            count(array_unique($authorTitles))
        );
    }
}
