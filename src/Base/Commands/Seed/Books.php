<?php

namespace Base\Commands\Seed;

class Books extends AbstractSeedCommand
{
    /**
     * @return string[]
     */
    protected function getTableNames()
    {
        return ['books'];
    }

    protected function seed()
    {
        foreach ([0, 1] as $lang) {
            $this->sql()->execute("
                INSERT INTO books (
                    id,
                    author_id,
                    language_id,
                    section_id,
                    title
                )
                SELECT
                    b.original_id_$lang,
                    b.author,
                    b.lang_$lang,
                    s.id,
                    b.title_$lang
                FROM
                    `_books` b
                    LEFT JOIN sections s
                        ON b.section = s.id
                GROUP BY
                    b.original_id_$lang,
                    b.author,
                    b.lang_$lang,
                    b.title_$lang
            ");
        }
    }
}
