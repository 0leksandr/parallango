<?php

namespace AppBundle\Entity\ItemsList;

use AppBundle\Entity\Author\Author;
use AppBundle\Entity\Parallango\Parallango;
use AppBundle\Entity\Section\Section;
use PHPUnit_Framework_TestCase;
use Utils\ServiceContainer;

class ListItemsProviderTest extends PHPUnit_Framework_TestCase
{
    /** @var ListItemsProvider */
    private $SUT;

    public function setUp()
    {
        $this->SUT = ServiceContainer::get('test')->get('list_item');
    }

    /**
     * @test
     */
    public function all_list_items_should_have_unique_url()
    {
        $entityTypes = [
            Author::ENTITY_TYPE,
            Section::ENTITY_TYPE,
            Parallango::ENTITY_TYPE,
        ];
        $listItems = array_mergev(map($entityTypes, function ($entityType) {
            $listItems = $this->getListItems(
                function ($index) use ($entityType) {
                    return $this->SUT->getListItems(
                        $entityType,
                        null,
                        null,
                        $index
                    );
                }
            );
            $this->assertNotEmpty($listItems);
            return $listItems;
        }));

        $this->assertAllValuesUnique(mpull($listItems, 'getUrl'));
    }

    /**
     * @test
     */
    public function all_parallangos_with_related_entity_should_have_unique_url()
    {
        $relatedEntities = [
            Author::ENTITY_TYPE => 3,
            Section::ENTITY_TYPE => 3,
        ];
        foreach ($relatedEntities as $relatedEntity => $relatedId) {
            $listItems = $this->getListItems(
                function ($index) use ($relatedEntity, $relatedId) {
                    return $this->SUT->getListItems(
                        Parallango::ENTITY_TYPE,
                        $relatedEntity,
                        $relatedId,
                        $index
                    );
                }
            );
            $this->assertAllValuesUnique(mpull($listItems, 'getUrl'));
        }
    }

    private function getListItems(callable $indexFunction)
    {
        $listItems = [];
        $index = 0;
        while (true) {
            $newItems = $indexFunction($index++);
            if (!$newItems) {
                break;
            }
            $listItems = array_merge($listItems, $newItems);
        }
        return $listItems;
    }

    private function assertAllValuesUnique(array $array)
    {
        $this->assertSame(
            [1],
            array_values(array_unique(array_count_values($array)))
        );
    }
}
