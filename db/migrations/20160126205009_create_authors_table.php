<?php

use AppBundle\Entity\Author\AuthorLanguageProperty;
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
        foreach (AuthorLanguageProperty::getAll() as $property) {
            $this->execute("
                INSERT INTO author_language_property (property_name)
                VALUES ('$property')
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
        foreach (AuthorLanguageProperty::getAll() as $propertyName) {
            $query = <<<'SQL'
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
                    a.:old_authors_column_name
                FROM
                    `_authors` a
                    JOIN languages l
                    JOIN author_language_property alp
                WHERE
                    l.code = 'en'
                    AND alp.property_name = :property_name;
SQL;
        }
    }
}
