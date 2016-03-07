<?php

namespace AppBundle\Entity\Page;

use PHPUnit_Framework_TestCase;
use Utils\ServiceContainer;

class PageRepositoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testPagesContents()
    {
        $serviceContainer = ServiceContainer::get('test');
        $parallangos = $serviceContainer->get('parallango')->getAll();
        $pageSizes = $serviceContainer->get('page_size')->getAll();
        foreach ($parallangos as $parallango) {
            foreach ($pageSizes as $pageSize) {
                $pages = $serviceContainer
                    ->get('page')
                    ->getByParallangoAndPageSize($parallango, $pageSize);
                $expectedBegin = '<tr><td>';
                $expectedEnd = '</td></tr>';
                foreach ($pages as $index => $page) {
                    $actualBegin = substr(
                        $page->getText(),
                        0,
                        strlen($expectedBegin)
                    );
                    $this->assertSame($expectedBegin, $actualBegin);
                    $actualEnd = substr(
                        $page->getText(),
                        - strlen($expectedEnd)
                    );
                    $this->assertSame(
                        $expectedEnd,
                        $actualEnd,
                        sprintf(
                            <<<'TEXT'
Parallango: %d,
page: %d,
last paragraph: %d,
ending: %s
TEXT
                            ,
                            $parallango->getId(),
                            $index + 1,
                            $page->getLastParagraph(),
                            $actualEnd
                        )
                    );
                    $this->assertSame(
                        $page->getLastParagraph()
                            - $page->getFirstParagraph() + 1,
                        substr_count($page->getText(), '</td><td>')
                    );
                }
            }
        }
    }
}
