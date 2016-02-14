<?php

use Phinx\Migration\AbstractMigration;

class CreateParallangosTable extends AbstractMigration
{
    public function up()
    {
        $this->execute(
            <<<'SQL'
            CREATE TABLE parallangos (
                id SERIAL PRIMARY KEY,
                left_book_id INTEGER NOT NULL REFERENCES books(id),
                right_book_id INTEGER NOT NULL REFERENCES books(id)
            );

            INSERT INTO parallangos (id, left_book_id, right_book_id)
            SELECT
                id,
                original_id_0,
                original_id_1
            FROM `_books`;
SQL
        );
    }

    public function down()
    {
        $this->execute(
            <<<'SQL'
            DROP TABLE parallangos;
SQL
        );
    }
}
