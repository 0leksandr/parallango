<?php

use Phinx\Migration\AbstractMigration;

class CreateBooksTable extends AbstractMigration
{
    public function up()
    {
        $this->execute(
            <<<'SQL'
            CREATE TABLE books (
                id SERIAL PRIMARY KEY,
                author_id INTEGER NOT NULL REFERENCES authors(id),
                language_id INTEGER NOT NULL REFERENCES languages(id),
                section_id INTEGER NOT NULL REFERENCES sections(id),
                title VARCHAR(256),
                original_id INTEGER NOT NULL
            );
SQL
        );

        foreach ([0, 1] as $lang) {
            $this->execute("
                INSERT INTO books (
                    author_id,
                    language_id,
                    section_id,
                    title,
                    original_id
                )
                SELECT
                    b.author,
                    b.lang_$lang,
                    b.section,
                    b.title_$lang,
                    b.original_id_$lang
                FROM `_books` b
            ");
        }
    }

    public function down()
    {
        $this->execute(
            <<<'SQL'
            DROP TABLE books;
SQL
        );
    }
}
