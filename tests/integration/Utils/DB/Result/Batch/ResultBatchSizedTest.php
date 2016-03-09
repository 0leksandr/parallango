<?php

namespace Utils\DB\Result\Batch;

use PHPUnit_Framework_TestCase;
use Utils\DB\ValuesList;
use Utils\ServiceContainer;

class ResultBatchSizedTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return array[]
     */
    public function values()
    {
        return [
            [[1, 10], [[5, 1, 5], [3, 6, 8], [2, 9, 10]]],
            [[1, 5], [[3, 1, 3], [3, 4, 5]]],
        ];
    }

    /**
     * @test
     * @dataProvider values
     * @param int[] $inserted
     * @param int[][] $requestedExpected
     */
    public function test(array $inserted, array $requestedExpected)
    {
        $sql = ServiceContainer::get('test')->get('sql');
        $sql->execute(
            <<<'SQL'
            CREATE TEMPORARY TABLE test (
                col1 INTEGER
            );
SQL
        );
        $sql->execute(
            <<<'SQL'
            INSERT INTO test (col1)
            VALUES :values;
SQL
            ,
            ['values' => new ValuesList(array_map(function ($value) {
                return [$value];
            }, range($inserted[0], $inserted[1])))]
        );
        $res = $sql->prepare(
            <<<'SQL'
            SELECT col1
            FROM test
            ORDER BY col1
SQL
        )->execute()->getResultBatchSized();
        foreach ($requestedExpected as $expReq) {
            $actual = $res->fetchBatch($expReq[0]);
            $expected = array_map(function ($value) {
                return ['col1' => $value];
            }, range($expReq[1], $expReq[2]));
            $this->assertSame($expected, $actual);
        }
        $this->assertSame(null, $res->fetchBatch(1));
        $sql->execute(
            <<<'SQL'
            DROP TABLE test;
SQL
        );
    }
}
