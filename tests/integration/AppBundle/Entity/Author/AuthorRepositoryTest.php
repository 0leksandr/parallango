<?php

namespace AppBundle\Entity\Author;

use AppBundle\Entity\Language\LanguageRepository;
use PHPUnit_Framework_TestCase;
use Utils\ServiceContainer;

class AuthorRepositoryTest extends PHPUnit_Framework_TestCase
{
    /** @var AuthorRepository */
    private $SUT;
    /** @var LanguageRepository */
    private $languageRepo;

    public function setUp()
    {
        $serviceContainer = ServiceContainer::get('test');
        $this->SUT = $serviceContainer->get('author');
        $this->languageRepo = $serviceContainer->get('language');
    }

    /**
     * @test
     */
    public function all_authors_should_have_unique_name()
    {
        foreach ($this->languageRepo->getActive() as $language) {
            $names = array_map(function (Author $author) use ($language) {
                $name = $author->getName($language);
                $this->assertNotEmpty($name);
                return $name;
            }, $this->SUT->getAll());

            $this->assertSame($names, array_unique($names));
        }
    }
}
