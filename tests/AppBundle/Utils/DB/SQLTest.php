<?php

namespace AppBundle\Utils\DB;

use PHPUnit_Framework_TestCase;

class SQLTest extends PHPUnit_Framework_TestCase
{
    /** @var SQL */
    private $SUT;

    public function setUp()
    {
        $this->SUT = new SQL();
    }

    /**
     * @return array[]
     */
    public function valuesToConvert()
    {
        return [
            [5],
            ['test'],
            [7.65],
            [true, 1],
            [null],
        ];
    }

    /**
     * @test
     * @dataProvider valuesToConvert
     * @param $varValue
     */
    public function testSelectSomethingReturnsSame($varValue)
    {
        $query = <<<'SQL'
            SELECT :var AS var
SQL;
        $varName = 'var';

        $params = [$varName => $varValue];

        $this->assertEquals(
            [[$varName => $varValue]],
            $this->SUT->getArray($query, $params)
        );

        $this->assertEquals(
            [$varName => $varValue],
            $this->SUT->getRow($query, $params)
        );

        $this->assertEquals(
            [$varValue],
            $this->SUT->getColumn($query, $params)
        );
        $this->assertEquals(
            [$varValue],
            $this->SUT->getColumn($query, $params, $varName)
        );

        $this->assertEquals(
            $varValue,
            $this->SUT->getSingle($query, $params)
        );
        $this->assertEquals(
            $varValue,
            $this->SUT->getSingle($query, $params, $varName)
        );
    }

    /**
     * @test
     * @dataProvider valuesToConvert
     * @param $passedVar
     * @param $expectedReturnVar
     */
    public function testConvertTypes($passedVar, $expectedReturnVar = null)
    {
        if ($expectedReturnVar === null) {
            $expectedReturnVar = $passedVar;
        }
        $query = <<<'SQL'
            SELECT :var AS var
SQL;
        $this->assertEquals(
            gettype($expectedReturnVar),
            gettype($this->SUT->getSingle($query, ['var' => $passedVar]))
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
}
