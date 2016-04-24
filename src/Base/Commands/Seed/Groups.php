<?php

namespace Base\Commands\Seed;

class Groups extends AbstractSeedCommand
{
    /**
     * @return string[]
     */
    protected function getTableNames()
    {
        return ['groups'];
    }

    protected function seed()
    {
        $this->sql()->execute(
            <<<'SQL'
            INSERT INTO groups (id)
            SELECT DISTINCT `group`
            FROM `_books`
            WHERE `group` IS NOT NULL
SQL
        );
        $this->sql()->execute(
            <<<'SQL'
            UPDATE books
            SET group_id = NULL
SQL
        );
        $this->sql()->execute(
            <<<'SQL'
            UPDATE books b1
            SET group_id = (
                SELECT DISTINCT `group`
                FROM `_books` b2
                WHERE
                    (
                        b1.id = b2.original_id_0
                        OR b1.id = b2.original_id_1
                    )
                    AND b2.`group` IS NOT NULL
            )
SQL
        );
    }
}
