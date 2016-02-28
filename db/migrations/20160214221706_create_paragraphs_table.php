<?php

use Phinx\Migration\AbstractMigration;

class CreateParagraphsTable extends AbstractMigration
{
    public function up()
    {
        $this->execute(
            <<<'SQL'
            CREATE TABLE paragraphs (
                id SERIAL PRIMARY KEY,
                parallango_id INTEGER NOT NULL REFERENCES parallangos(id),
                `order` INTEGER NOT NULL,
                position_begin INTEGER NOT NULL,
                position_end INTEGER NOT NULL
            );
SQL
        );
    }

    public function down()
    {
        $this->execute(
            <<<'SQL'
            DROP TABLE paragraphs;
SQL
        );
    }
}
