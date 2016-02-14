<?php

use Utils\ServiceContainer;

require_once __DIR__ . '/../src/Utils/Utils.php';

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
                'author_id' => ['authors.id', true],
                'language_id' => ['languages.id', true],
                'property_id' => ['author_language_property.id', true],
            ],
            'section_titles' => [
                'section_id' => ['sections.id', true],
                'language_id' => ['languages.id', true],
            ],
            'books' => [
                'author_id' => ['authors.id', true],
                'language_id' => ['languages.id', true],
                'section_id' => ['sections.id', false],
            ],
        ] as $table => $columns) {
            foreach ($columns as $column => $foreignColumn) {
                list($foreignColumn, $notNull) = $foreignColumn;
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

                if ($notNull === false) {
                    $thisValues = array_filter($thisValues, function ($value) {
                        return $value !== null;
                    });
                }

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
