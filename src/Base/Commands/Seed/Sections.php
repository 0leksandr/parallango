<?php

namespace Base\Commands\Seed;

class Sections extends AbstractSeedCommand
{
    /**
     * @return string[]
     */
    protected function getTableNames()
    {
        return ['sections', 'section_titles'];
    }

    protected function seed()
    {
        $this->sql()->execute(
            <<<'SQL'
            INSERT INTO sections(id)
            SELECT id
            FROM `_sections` s
            WHERE
                (
                    s.title_en <> ''
                    OR s.title_uk <> ''
                    OR s.title_ru <> ''
                )
                AND s.count > 0;
SQL
        );
        foreach ($this->sql()->getArray(
            <<<'SQL'
            SELECT id, code
            FROM languages
SQL
        ) as $language) {
            $languageId = $language['id'];
            $languageCode = $language['code'];
            $this->sql()->execute("
                INSERT INTO section_titles(section_id, language_id, title)
                SELECT
                    s.id,
                    $languageId,
                    s.title_$languageCode
                FROM `_sections` s
                WHERE
                    s.title_$languageCode <> ''
                    AND s.title_$languageCode IS NOT NULL
                    AND s.count > 0
            ");
        }
    }
}
