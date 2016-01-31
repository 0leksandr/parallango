<?php

use Phinx\Migration\AbstractMigration;

class CreateAuthorsTable extends AbstractMigration
{
    public function up()
    {
        $this->execute(
            <<<'SQL'
            CREATE TABLE authors (
                id SERIAL PRIMARY KEY
            );

            INSERT INTO authors (id)
            SELECT id
            FROM _authors;
SQL
        );

        $this->execute(
            <<<'SQL'
            CREATE TABLE author_language_property (
                id SERIAL PRIMARY KEY,
                property_name VARCHAR(32)
            );
SQL
        );
        $authorLanguageProperties = [
            'name' => 'name',
            'pseudonym' => 'pseudonym',
            'first_name' => 'first name',
            'last_name' => 'last name',
        ];
        foreach ($authorLanguageProperties as $propertyName) {
            $this->execute("
                INSERT INTO author_language_property (property_name)
                VALUES ('$propertyName')
            ");
        }

        $this->execute(
            <<<'SQL'
            CREATE TABLE author_language_properties (
                id SERIAL PRIMARY KEY,
                author_id INTEGER NOT NULL REFERENCES authors(id),
                language_id INTEGER NOT NULL REFERENCES languages(id),
                property_id INTEGER NOT NULL
                    REFERENCES author_language_property(id),
                property_value VARCHAR(128)
            );
SQL
        );
        foreach ($authorLanguageProperties as $oldColumnName => $propertyName) {
            foreach (['en', 'uk', 'ru'] as $languageCode) {
                $this->execute("
                    INSERT INTO author_language_properties (
                        author_id,
                        language_id,
                        property_id,
                        property_value
                    )
                    SELECT
                        a.id,
                        l.id,
                        alp.id,
                        a." . ($oldColumnName . '_' . $languageCode) . "
                    FROM
                        `_authors` a
                        JOIN languages l
                        JOIN author_language_property alp
                    WHERE
                        l.code = '$languageCode'
                        AND alp.property_name = '$propertyName';
                ");
            }
        }

        $this->execute(
            <<<'SQL'
            INSERT INTO author_language_property (property_name)
            VALUES ('wiki page');

            INSERT INTO author_language_properties (
                author_id,
                language_id,
                property_id,
                property_value
            )
            SELECT
                a.id,
                l.id,
                alp.id,
                a.wiki_page_ru
            FROM
                _authors a
                JOIN languages l
                JOIN author_language_property alp
            WHERE
                l.code = 'ru'
                AND alp.property_name = 'wiki page';
SQL
        );
    }

    public function down()
    {
        $this->execute(
            <<<'SQL'
            DROP TABLE author_language_properties;
            DROP TABLE author_language_property;
            DROP TABLE authors;
SQL
        );
    }
}
