<?php

namespace Utils\DB;

use PHPUnit_Framework_TestCase;
use Utils\ServiceContainer;

class SQLTest extends PHPUnit_Framework_TestCase
{
    /** @var SQL */
    private $SUT;

    public function setUp()
    {
        $this->SUT = ServiceContainer::get('test')->get('sql');
    }

    /**
     * @return array[]
     */
    public function valuesToConvert()
    {
        return [
            [5],
            ['test'],
            [7.65, '7.65'],
            [true, 1],
            [null],
            ['test "test" \'test\' \\'],
            ["test 'test' \"test\" \\"],
        ];
    }

    /**
     * @return array[]
     */
    public function valuesInArray()
    {
        return [
            [10, [1, 2, 3], 0],
            [10, [1, 2, 10], 1],
        ];
    }

    /**
     * @test
     * @dataProvider valuesToConvert
     * @param mixed $varValue
     * @param mixed|null $expectedReturnVar
     */
    public function testSelectSomethingReturnsSame(
        $varValue,
        $expectedReturnVar = null
    ) {
        if ($expectedReturnVar === null) {
            $expectedReturnVar = $varValue;
        }
        $query = <<<'SQL'
            SELECT :var AS var
SQL;
        $varName = 'var';

        $params = [$varName => $varValue];

        $this->assertSame(
            [[$varName => $expectedReturnVar]],
            $this->SUT->getArray($query, $params)
        );

        $this->assertSame(
            [$varName => $expectedReturnVar],
            $this->SUT->getRow($query, $params)
        );

        $this->assertSame(
            [$expectedReturnVar],
            $this->SUT->getColumn($query, $params)
        );
        $this->assertSame(
            [$expectedReturnVar],
            $this->SUT->getColumn($query, $params, $varName)
        );

        $this->assertSame(
            $expectedReturnVar,
            $this->SUT->getSingle($query, $params)
        );
        $this->assertSame(
            $expectedReturnVar,
            $this->SUT->getSingle($query, $params, $varName)
        );
    }

    /**
     * @test
     */
    public function testLiteral()
    {
        $this->assertSame(
            4,
            $this->SUT->getSingle(':query', ['query' => new Literal(<<<'SQL'
                SELECT 4
SQL
            )])
        );
    }

    /**
     * @test
     */
    public function testFuckingBool()
    {
        $this->assertSame(
            1,
            $this->SUT->getSingle(
                <<<'SQL'
                SELECT TRUE
SQL
            )
        );
    }

    /**
     * @test
     */
    public function testFloat()
    {
        $result = $this->SUT->getSingle(
            <<<'SQL'
                SELECT :aa + :bb
SQL
            ,
            [
                'aa' => 1.23,
                'bb' => 4.56,
            ]
        );
        $this->assertSame(5.79, $result);
    }

    /**
     * @test
     */
    public function testArray()
    {
        $values = [
            [
                'id' => 1,
                'text' => 'test1',
            ],
            [
                'id' => 2,
                'text' => 'test2',
            ],
            [
                'id' => 3,
                'text' => 'test3',
            ],
        ];
        $valuesWithoutKeys = array_map(function (array $array) {
            return array_values($array);
        }, $values);
        $this->SUT->execute(
            <<<'SQL'
            CREATE TEMPORARY TABLE test (
                id INTEGER NOT NULL,
                text TEXT NOT NULL
            );

            INSERT INTO test (id, text)
            VALUES :values;
SQL
            ,
            ['values' => new ValuesList($valuesWithoutKeys)]
        );
        $result = $this->SUT->getArray(
            <<<'SQL'
            SELECT * FROM test;
SQL
        );
        $this->SUT->execute(
            <<<'SQL'
            DROP TABLE test;
SQL
        );
        $this->assertSame($values, $result);
    }

    /**
     * @test
     * @dataProvider valuesInArray
     * @param mixed $value
     * @param array $array
     * @param mixed $expect
     */
    public function testInArray($value, array $array, $expect)
    {
        $this->assertSame(
            $expect,
            $this->SUT->getSingle(
                <<<'SQL'
                SELECT :value IN :array
SQL
                ,
                [
                    'value' => $value,
                    'array' => $array,
                ]
            )
        );
    }
}
