<?php

namespace AppBundle\Entity\Parallango;

use AppBundle\Entity\Language\LanguageRepository;
use PHPUnit_Framework_TestCase;
use Utils\ServiceContainer;

class ParallangoRepositoryTest extends PHPUnit_Framework_TestCase
{
    /** @var ParallangoRepository */
    private $SUT;
    /** @var LanguageRepository */
    private $languageRepo;

    public function setUp()
    {
        $serviceContainer = ServiceContainer::get('test');
        $this->SUT = $serviceContainer->get('parallango');
        $this->languageRepo = $serviceContainer->get('language');
    }

    /**
     * @test
     */
    public function all_parallangos_should_have_unique_title_and_author()
    {
        $this->markTestSkipped('-_-');
        foreach ($this->languageRepo->getActive() as $language) {
            $authorsTitles = array_map(
                function (Parallango $parallango) use ($language) {
                    return sprintf(
                        '%d ### %s',
                        $parallango->getAuthor()->getId(),
                        $parallango->getTitle($language)
                    );
                },
                $this->SUT->getAll()
            );

            $this->assertSame(
                [],
                array_filter(
                    array_count_values($authorsTitles),
                    function ($int) {
                        return $int > 1;
                    }
                )
            );
        }
    }
}
