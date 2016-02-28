<?php

use Utils\DB\SQL;
use Utils\ServiceContainer;

require_once __DIR__ . '/../../src/Utils/Utils.php';

class ParagraphsTest extends PHPUnit_Framework_TestCase
{
    /** @var SQL */
    private $sql;

    public function setUp()
    {
        $this->sql = ServiceContainer::get('test')->get('sql');
    }

    /**
     * @test
     */
    public function paragraphsShouldEndAndBeginOneAfterAnother()
    {
        $memoryLimit = ini_get('memory_limit');
        ini_set('memory_limit', '1G');

        $allParallangoIds = $this->sql->getColumn(
            <<<'SQL'
            SELECT id
            FROM parallangos
SQL
        );
        $chunks = array_chunk($allParallangoIds, 100);
        foreach ($chunks as $chunkIndex => $parallangoIds) {
            echo (sprintf(
                '%s:%s %d/%d',
                __FILE__,
                __LINE__,
                $chunkIndex,
                count($chunks)
            ) . PHP_EOL);
            $paragraphsGrouped = igroup($this->sql->getArray(
                <<<'SQL'
                SELECT
                    id,
                    parallango_id,
                    `order`,
                    position_begin,
                    position_end
                FROM paragraphs
                WHERE parallango_id IN :parallango_ids
                ORDER BY id
SQL
                ,
                ['parallango_ids' => $parallangoIds]
            ), 'parallango_id');
            foreach ($paragraphsGrouped as $parallangoId => $paragraphs) {
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
                    $this->assertSame($prevOrder + 1, $paragraph['order']);
                    $prevOrder = $paragraph['order'];
                }
            }
        }

        ini_set('memory_limit', $memoryLimit);
    }

    /**
     * @test
     */
    public function allParallangosShouldHaveParagraphs()
    {
        $parallangoIds = $this->sql->getColumn(
            <<<'SQL'
            SELECT id
            FROM parallangos
            ORDER BY id
SQL
        );
        $paragraphParallangoIds = $this->sql->getColumn(
            <<<'SQL'
            SELECT DISTINCT parallango_id
            FROM paragraphs
            ORDER BY parallango_id
SQL
        );
        $this->assertSame(
            $parallangoIds,
            $paragraphParallangoIds,
            sprintf(
                'These parallangos don\'t have paragraphs: %s',
                implode(
                    ', ',
                    array_diff($parallangoIds, $paragraphParallangoIds)
                )
            )
        );

        foreach ($parallangoIds as $parallangoId) {
            $paragraphs = $this->sql->getColumn(
                <<<'SQL'
                SELECT `order`
                FROM paragraphs
                WHERE parallango_id = :parallango_id
SQL
                ,
                ['parallango_id' => $parallangoId]
            );
            $this->assertGreaterThan(0, count($paragraphs));
            $this->assertSame(range(0, max($paragraphs)), $paragraphs);
        }
    }
}
