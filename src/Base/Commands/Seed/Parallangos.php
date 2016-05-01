<?php

namespace Base\Commands\Seed;

class Parallangos extends AbstractSeedCommand
{
    /**
     * @return string[]
     */
    protected function getTableNames()
    {
        return ['parallangos'];
    }

    protected function seed()
    {
        $this->sql()->execute(
            <<<'SQL'
            INSERT INTO parallangos (id, left_book_id, right_book_id)
            SELECT
                id,
                original_id_0,
                original_id_1
            FROM `_books`
            ORDER BY id
SQL
        );
    }
}
