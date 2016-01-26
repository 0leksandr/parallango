<?php

namespace AppBundle\Entity\Author;

class AuthorLanguageProperty
{
    const PSEUDONYM = 'Pseudonym';
    const FIRST_NAME = 'First name';
    const LAST_NAME = 'Last name';
    const WIKI_PAGE = 'Wiki page';

    /**
     * @return string[]
     */
    public static function getAll()
    {
        return [
            self::PSEUDONYM,
            self::FIRST_NAME,
            self::LAST_NAME,
            self::WIKI_PAGE,
        ];
    }
}
