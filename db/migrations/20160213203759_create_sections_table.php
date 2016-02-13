<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class CreateSectionsTable extends AbstractMigration
{
    public function up()
    {
        $this->execute(
            <<<'SQL'
            CREATE TABLE sections (
                id SERIAL PRIMARY KEY
            );

            INSERT INTO sections(id)
            SELECT id
            FROM `_sections`;

            CREATE TABLE section_titles (
                id SERIAL PRIMARY KEY,
                section_id INTEGER NOT NULL REFERENCES sections(id),
                language_id INTEGER NOT NULL REFERENCES  languages(id),
                title VARCHAR(64)
            );
SQL
        );

        $pdo = $this->getPdo();
        $stmt = $pdo->prepare(
            <<<'SQL'
            SELECT id, code
            FROM languages
SQL
        );
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $languageId = $row['id'];
            $languageCode = $row['code'];
            $this->execute("
                INSERT INTO section_titles(section_id, language_id, title)
                SELECT
                    s.id,
                    $languageId,
                    s.title_$languageCode
                FROM `_sections` s
                WHERE
                    s.title_$languageCode <> ''
                    AND s.title_$languageCode IS NOT NULL
            ");
        }
    }

    public function down()
    {
        $this->execute(
            <<<'SQL'
            DROP TABLE section_titles;
            DROP TABLE sections;
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
