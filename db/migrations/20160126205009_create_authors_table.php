<?php

use AppBundle\Entity\Author\Author;
use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class CreateAuthorsTable extends AbstractMigration
{
    public function up()
    {
        $pdo = $this->getPdo();

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
        $stmt = $pdo->prepare(
            <<<'SQL'
            INSERT INTO author_language_property (property_name)
            VALUES (:property_name)
SQL
        );
        foreach (Author::getPropertyNames() as $propertyName) {
            $stmt->bindValue('property_name', $propertyName);
            $stmt->execute();
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
        foreach (Author::getPropertyNames() as $propertyName) {
            foreach (['en', 'uk', 'ru'] as $languageCode) {
                if ($propertyName !== 'wiki_page' || $languageCode === 'ru') {
                    $oldColumn = $propertyName . '_' . $languageCode;
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
                            a." . $oldColumn . "
                        FROM
                            `_authors` a
                            JOIN languages l
                            JOIN author_language_property alp
                        WHERE
                            l.code = '$languageCode'
                            AND alp.property_name = '$propertyName'
                            AND a." . $oldColumn . " <> '';
                    ");
                }
            }
        }
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

    /**
     * @return PDO
     * @throws Exception
     */
    private function getPdo()
    {
        $adapter = $this->getAdapter();
        if ($adapter instanceof MysqlAdapter) {
            return $adapter->getConnection();
        }
        throw new Exception('Not Mysql Adapter');
    }
}
