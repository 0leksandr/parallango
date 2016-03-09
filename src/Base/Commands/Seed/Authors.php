<?php

namespace Base\Commands\Seed;

use AppBundle\Entity\Author\Author;
use AppBundle\Entity\Language\Language;
use Utils\DB\ValuesList;

class Authors extends AbstractSeedCommand
{
    /**
     * @return string[]
     */
    protected function getTableNames()
    {
        return [
            'authors',
            'author_language_property',
            'author_language_properties',
        ];
    }

    protected function seed()
    {
        $this->seedAuthors();
        $this->seedAuthorLanguageProperty();
        $this->seedAuthorLanguageProperties();
    }

    private function seedAuthors()
    {
        $this->sql()->execute(
            <<<'SQL'
            INSERT INTO authors (id)
            SELECT id
            FROM _authors;
SQL
        );
    }

    private function seedAuthorLanguageProperty()
    {
        $this->sql()->execute(
            <<<'SQL'
            INSERT INTO author_language_property (property_name)
            VALUES :values;
SQL
            ,
            ['values' => new ValuesList(array_map(function ($value) {
                return [$value];
            }, Author::getPropertyNames()))]
        );
    }

    private function seedAuthorLanguageProperties()
    {
        $languageCodes = $this->sql()->getColumn(
            <<<'SQL'
            SELECT code
            FROM languages
SQL
        );
        foreach (Author::getPropertyNames() as $propertyName) {
            foreach ($languageCodes as $languageCode) {
                if (
                    $propertyName !== Author::WIKI_PAGE
                    || $languageCode === Language::RU
                ) {
                    $oldColumn = $propertyName . '_' . $languageCode;
                    $this->sql()->execute("
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
                            a.$oldColumn
                        FROM
                            `_authors` a
                            JOIN languages l
                            JOIN author_language_property alp
                        WHERE
                            l.code = '$languageCode'
                            AND alp.property_name = '$propertyName'
                            AND a.$oldColumn <> '';
                    ");
                }
            }
        }
    }
}
