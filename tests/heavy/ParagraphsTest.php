<?php

use Utils\DB\SQL;
use Utils\ServiceContainer;

require_once __DIR__ . '/../../src/Utils/Utils.php';

class ParagraphsTest extends PHPUnit_Framework_TestCase
{
    /**
     * ~3min
     *
     * @test
     */
    public function paragraphsShouldEndAndBeginOneAfterAnother()
    {
        $res = ServiceContainer::get('test')->get('sql')->prepare(
            <<<'SQL'
            SELECT
                id,
                parallango_id,
                `order`,
                position_begin,
                position_end
            FROM
                paragraphs
            ORDER BY
                parallango_id,
                id
SQL
        )->execute()->getResultBatchIndexed('parallango_id');
        while ($paragraphs = $res->fetchBatchArray()) {
            $parallangoIds = array_unique(ipull($paragraphs, 'parallango_id'));
            $this->assertSame(1, count($parallangoIds));
            $parallangoId = head($parallangoIds);
            $prevPositionEnd = strlen('<table>') - 1;
            $prevOrder = -1;
            foreach ($paragraphs as $paragraph) {
                $this->assertSame(
                    $prevPositionEnd + 1,
                    $paragraph['position_begin'],
                    sprintf(
                        'Parallango: %d, paragraph: %d',
                        $parallangoId,
                        $paragraph['id']
                    )
                );
                $prevPositionEnd = $paragraph['position_end'];
                $this->assertSame(++$prevOrder, $paragraph['order']);
            }
        }
    }
}
