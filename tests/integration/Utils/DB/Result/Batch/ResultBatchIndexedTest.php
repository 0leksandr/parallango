<?php

namespace Utils\DB\Result\Batch;

use PHPUnit_Framework_TestCase;
use Utils\DB\SQL;
use Utils\DB\ValuesList;
use Utils\ServiceContainer;

require_once __DIR__ . '/../../../../../../src/Utils/Utils.php';

class ResultBatchIndexedTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function test()
    {
        $chunks = [
            [
                ['col1' => 1, 'col2' => 'test1'],
                ['col1' => 1, 'col2' => 'test2'],
                ['col1' => 1, 'col2' => 'test3'],
            ],
            [
                ['col1' => 2, 'col2' => 'test4'],
                ['col1' => 2, 'col2' => 'test5'],
            ],
            [
                ['col1' => 3, 'col2' => 'test6'],
            ],
            [
                ['col1' => 5, 'col2' => 'test7'],
            ],
            [
                ['col1' => 3, 'col2' => 'test8'],
            ],
        ];
        $sql = ServiceContainer::get('test')->get('sql');
        $sql->execute(
            <<<'SQL'
            CREATE TEMPORARY TABLE test (
                col1 INTEGER,
                col2 TEXT
            );
SQL
        );
        $sql->execute(
            <<<'SQL'
            INSERT INTO test (col1, col2)
            VALUES :values;
SQL
            ,
            [
                'values' => new ValuesList(
                    array_mergev(
                        array_map(function ($chunk) {
                            return array_map(function ($subChunk) {
                                return array_values($subChunk);
                            }, $chunk);
                        }, $chunks)
                    )
                ),
            ]
        );
        $res = $sql->prepare(
            <<<'SQL'
            SELECT col1, col2
            FROM test
SQL
        )->execute()->getResultBatchIndexed('col1');
        $actual = [];
        while (($chunk = $res->fetchBatchArray()) !== null) {
            $actual[] = $chunk;
        }
        $this->assertSame($chunks, $actual);

        $sql->execute(
            <<<'SQL'
            DROP TABLE test;
SQL
        );
    }
}
