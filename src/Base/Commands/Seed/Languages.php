<?php

namespace Base\Commands\Seed;

use AppBundle\Entity\Language\Language;

class Languages extends AbstractSeedCommand
{
    /**
     * @return string[]
     */
    protected function getTableNames()
    {
        return ['languages'];
    }

    protected function seed()
    {
        $this->sql()->execute(
            <<<'SQL'
            INSERT INTO `languages` (code, is_active)
            SELECT
                lang,
                lang IN :active_language_codes
            FROM `_languages`
SQL
            ,
            ['active_language_codes' => [Language::EN, Language::RU]]
        );
    }
}
