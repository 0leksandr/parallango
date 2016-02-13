<?php

use Phinx\Migration\AbstractMigration;

class AddActiveToLanguages extends AbstractMigration
{
    public function up()
    {
        $this->execute(
            <<<'SQL'
            ALTER TABLE languages
            ADD COLUMN is_active BOOLEAN NOT NULL DEFAULT FALSE;

            UPDATE languages
            SET is_active = TRUE
            WHERE code IN ('en', 'ru');
SQL
        );
    }

    public function down()
    {
        $this->execute(
            <<<'SQL'
            ALTER TABLE languages
            DROP COLUMN is_active;
SQL
        );
    }
}
