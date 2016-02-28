<?php

use Utils\ServiceContainer;

require_once __DIR__ . '/../../src/Utils/Utils.php';

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
                'author_id' => ['authors', 'id', true],
                'language_id' => ['languages', 'id', true],
                'property_id' => ['author_language_property', 'id', true],
            ],
            'section_titles' => [
                'section_id' => ['sections', 'id', true],
                'language_id' => ['languages', 'id', true],
            ],
            'books' => [
                'author_id' => ['authors', 'id', true],
                'language_id' => ['languages', 'id', true],
                'section_id' => ['sections', 'id', false],
            ],
            'parallangos' => [
                'left_book_id' => ['books', 'id', true],
                'right_book_id' => ['books', 'id', true],
            ],
            'paragraphs' => [
                'parallango_id' => ['parallangos', 'id', true],
            ],
        ] as $table => $columns) {
            foreach ($columns as $column => $foreign) {
                list($foreignTable, $foreignColumn, $notNull) = $foreign;

                $thisValues = $sql->getColumn("
                    SELECT DISTINCT $column
                    FROM $table
                ");
                $foreignValues = $sql->getColumn("
                    SELECT DISTINCT $foreignColumn
                    FROM $foreignTable
                ");

                if ($notNull === false) {
                    $thisValues = array_filter($thisValues, function ($value) {
                        return $value !== null;
                    });
                }

                $this->assertGreaterThan(
                    0,
                    count($thisValues),
                    'Table: ' . $table
                );
                $this->assertGreaterThan(
                    0,
                    count($foreignValues),
                    'Table: ' . $foreignTable
                );

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
