<?php

namespace tests\integration\AppBundle;

use PHPUnit_Framework_TestCase;
use Utils\ServiceContainer;

class GroupsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function all_books_in_group_should_have_same_author()
    {
        // TODO: books in different languages should belong to different groups
        $booksGroups = array_reduce(
            ServiceContainer::get('test')->get('sql')->getArray(
                <<<'SQL'
                SELECT
                    group_id,
                    language_id,
                    GROUP_CONCAT(id) AS book_ids,
                    GROUP_CONCAT(author_id) AS author_ids
                FROM
                    books
                WHERE
                    group_id IS NOT NULL
                GROUP BY
                    group_id,
                    language_id
SQL
            ), function ($result, $row) {
                $res = [];
                foreach ([
                    'book_ids',
                    'author_ids',
                ] as $columnName) {
                    $res[$columnName] = array_map(
                        'intval',
                        explode(',', $row[$columnName])
                    );
                }
                $result[$row['group_id'] . '-' . $row['language_id']] = $res;
                return $result;
            }
        );
        foreach ($booksGroups as $group) {
            $this->assertEquals(1, count(array_unique($group['author_ids'])));
        }
    }
}
