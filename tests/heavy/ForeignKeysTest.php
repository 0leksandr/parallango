<?php

namespace heavy;

use PHPUnit_Framework_TestCase;
use Utils\ServiceContainer;

class ForeignKeysTest extends PHPUnit_Framework_TestCase
{
    /**
     * ~3.5min
     *
     * @test
     */
    public function test()
    {
        $sql = ServiceContainer::get('test')->get('sql');
        foreach ([
            'materialized_pages' => [
                'paragraph_id' => ['paragraphs', 'id', 100000],
            ],
        ] as $table => $columns) {
            foreach ($columns as $column => $foreign) {
                $thisValues = $sql->getColumn("
                    SELECT DISTINCT $column
                    FROM $table
                ");
                $this->assertGreaterThan(
                    0,
                    count($thisValues),
                    sprintf('Table %s is empty', $table)
                );

                list($foreignTable, $foreignColumn, $batchSize) = $foreign;
                $foreignRes = $sql
                    ->prepare("
                        SELECT DISTINCT $foreignColumn
                        FROM $foreignTable
                    ")
                    ->execute()
                    ->getResultBatchSized();
                $nonPresent = $thisValues;
                while (
                    $foreignValues = $foreignRes->fetchBatch($batchSize)
                ) {
                    $foreignValues = ipull($foreignValues, $foreignColumn);
                    $nonPresent = array_diff($nonPresent, $foreignValues);
                }
                $this->assertSame(
                    0,
                    count($nonPresent),
                    sprintf(
                        'Values in %s.%s, that are not present in %s.%s: %s',
                        $table,
                        $column,
                        $foreignTable,
                        $foreignColumn,
                        implode(', ', $nonPresent)
                    )
                );
            }
        }
    }
}
