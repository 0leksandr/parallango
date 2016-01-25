<?php

use Phinx\Migration\AbstractMigration;

class CreateLanguagesTable extends AbstractMigration
{
    public function up()
    {
        $this->execute(
            <<<'SQL'
            CREATE TABLE `languages` (
                id SERIAL PRIMARY KEY,
                code VARCHAR(2)
            );

            INSERT INTO `languages` (code)
            SELECT lang
            FROM `_languages`
SQL
        );
    }

    public function down()
    {
        $this->execute(
            <<<'SQL'
            DROP TABLE languages;
SQL
        );
    }
}
