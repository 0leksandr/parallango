<?php

use Utils\ServiceContainer;

require_once __DIR__ . '/../../src/Utils/Utils.php';

class DBTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testForeignKeys()
    {
        $sql = ServiceContainer::get('test')->get('sql');
        foreach ([
            'author_language_properties' => [
                'author_id' => ['authors', 'id', true, true],
                'language_id' => ['languages', 'id', true, false],
                'property_id' => ['author_language_property', 'id', true, true],
            ],
            'section_titles' => [
                'section_id' => ['sections', 'id', true, true],
                'language_id' => ['languages', 'id', true, false],
            ],
            'books' => [
                'author_id' => ['authors', 'id', true, true],
                'language_id' => ['languages', 'id', true, false],
                'section_id' => ['sections', 'id', false, true],
                'group_id' => ['groups', 'id', false, true],
            ],
            'parallangos' => [
                'left_book_id' => ['books', 'id', true, false],
                'right_book_id' => ['books', 'id', true, false],
            ],
            'paragraphs' => [
                'parallango_id' => ['parallangos', 'id', true, true],
            ],
            'materialized_pages' => [
                'parallango_id' => ['parallangos', 'id', true, true],
                'page_size_id' => ['page_sizes', 'id', true, null, true],
            ],
            'mat_nr_books_authors' => [
                'author_id' => ['authors' ,'id', true, true],
                'language1_id' => ['languages', 'id', true, false],
                'language2_id' => ['languages', 'id', true, false],
            ],
            'mat_nr_books_sections' => [
                'section_id' => ['sections' ,'id', true, true],
                'language1_id' => ['languages', 'id', true, false],
                'language2_id' => ['languages', 'id', true, false],
            ],
        ] as $table => $columns) {
            foreach ($columns as $column => $foreign) {
                list($foreignTable, $foreignColumn, $notNull, $shouldMatchFully)
                    = $foreign;

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

                $thisForeign = [
                    'this' => [
                        'table' => $table,
                        'column' => $column,
                        'values' => $thisValues,
                    ],
                    'foreign' => [
                        'table' => $foreignTable,
                        'column' => $foreignColumn,
                        'values' => $foreignValues,
                    ],
                ];
                $toTest = [
                    [
                        'left' => $thisForeign['this'],
                        'right' => $thisForeign['foreign'],
                    ],
                ];
                if ($shouldMatchFully) {
                    $toTest[] = [
                        'left' => $thisForeign['foreign'],
                        'right' => $thisForeign['this'],
                    ];
                }
                foreach ($toTest as $item) {
                    $nonPresent = array_diff(
                        $item['left']['values'],
                        $item['right']['values']
                    );
                    $this->assertSame(
                        0,
                        count($nonPresent),
                        sprintf(
                            'Values in %s.%s, that are not present in %s.%s: '
                                . '%s',
                            $item['left']['table'],
                            $item['left']['column'],
                            $item['right']['table'],
                            $item['right']['column'],
                            implode(', ', $nonPresent)
                        )
                    );
                }
            }
        }
    }
}
