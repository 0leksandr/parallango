<?php

use Utils\ServiceContainer;

class DBTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function checkForeignKeys()
    {
        $sql = ServiceContainer::get('test')->get('sql');
        foreach ([
            'author_language_properties' => [
                'author_id' => 'authors.id',
                'language_id' => 'languages.id',
                'property_id' => 'author_language_property.id',
            ],
            'section_titles' => [
                'section_id' => 'sections.id',
                'language_id' => 'languages.id',
            ],
        ] as $table => $columns) {
            foreach ($columns as $column => $foreignColumn) {
                list($foreignTable, $foreignColumn) =
                    explode('.', $foreignColumn);
                $thisValues = $sql->getColumn("
                    SELECT $column
                    FROM $table
                ");
                $foreignValues = $sql->getColumn("
                    SELECT $foreignColumn
                    FROM $foreignTable
                ");
                $nonPresent = array_diff($thisValues, $foreignValues);
                $this->assertEquals(
                    0,
                    count($nonPresent),
                    sprintf(
                        '%s.%s has value %s, that is not present in %s.%s',
                        $table,
                        $column,
                        head($nonPresent),
                        $foreignTable,
                        $foreignColumn
                    )
                );
            }
        }
    }
}
