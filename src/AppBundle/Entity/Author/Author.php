<?php

namespace AppBundle\Entity\Author;

use AppBundle\Entity\Identifiable;
use AppBundle\Entity\Language\Language;

class Author extends Identifiable
{
    const NAME = 'name';
    const PSEUDONYM = 'pseudonym';
    const FIRST_NAME = 'first_name';
    const LAST_NAME = 'last_name';
    const WIKI_PAGE = 'wiki_page';

    /** @var array[] */
    private $translatedProperties = [];

//    /** @var MultiTranslation */
//    private $name;
//    /** @var MultiTranslation */
//    private $pseudonym;
//    /** @var MultiTranslation */
//    private $firstName;
//    /** @var MultiTranslation */
//    private $lastName;
//    /** @var MultiTranslation */
//    private $wikiPage;
//
//    /**
//     * @param int $id
//     */
//    public function __construct($id)
//    {
//        parent::__construct($id);
//        $this->name = new MultiTranslation();
//        $this->pseudonym = new MultiTranslation();
//        $this->firstName = new MultiTranslation();
//        $this->lastName = new MultiTranslation();
//    }
//
//    /**
//     * @param Language $language
//     * @return string
//     */
//    public function getName(Language $language)
//    {
//        return $this->name->getValue($language);
//    }
//
//    /**
//     * @param Language $language
//     * @return string
//     */
//    public function getPseudonym(Language $language)
//    {
//        return $this->pseudonym->getValue($language);
//    }
//
//    /**
//     * @param Language $language
//     * @return string
//     */
//    public function getFirstName(Language $language)
//    {
//        return $this->firstName->getValue($language);
//    }
//
//    /**
//     * @param Language $language
//     * @return string
//     */
//    public function getLastName(Language $language)
//    {
//        return $this->lastName->getValue($language);
//    }
//
//    /**
//     * @param Language $language
//     * @return string
//     */
//    public function getWikiPage(Language $language)
//    {
//        return $this->wikiPage->getValue($language);
//    }
//
//    /**
//     * @param Language $language
//     * @param string $name
//     * @return $this
//     */
//    public function addName(Language $language, $name)
//    {
//        $this->name->addValue($language, $name);
//        return $this;
//    }
//
//    /**
//     * @param Language $language
//     * @param string $pseudonym
//     * @return $this
//     */
//    public function addPseudonym(Language $language, $pseudonym)
//    {
//        $this->pseudonym->addValue($language, $pseudonym);
//        return $this;
//    }
//
//    /**
//     * @param Language $language
//     * @param string $firstName
//     * @return $this
//     */
//    public function addFirstName(Language $language, $firstName)
//    {
//        $this->firstName->addValue($language, $firstName);
//        return $this;
//    }
//
//    /**
//     * @param Language $language
//     * @param string $lastName
//     * @return $this
//     */
//    public function addLastName(Language $language, $lastName)
//    {
//        $this->lastName->addValue($language, $lastName);
//        return $this;
//    }

    /**
     * @param string $propertyName
     * @param Language $language
     * @return string
     * @throws \Exception
     */
    public function get($propertyName, Language $language)
    {
        $this->checkPropertyName($propertyName);
        if (!isset(
            $this->translatedProperties[$language->getCode()][$propertyName]
        )) {
            throw new \Exception(sprintf(
                'Can not get property %s of author#%d',
                $propertyName,
                $this->getId()
            ));
        }
        return $this->translatedProperties[$language->getCode()][$propertyName];
    }

    /**
     * @param string $propertyName
     * @param Language $language
     * @param string $propertyValue
     * @return $this
     */
    public function set($propertyName, Language $language, $propertyValue)
    {
        $this->checkPropertyName($propertyName);
        $this->translatedProperties[$language->getCode()][$propertyName] =
            $propertyValue;
        return $this;
    }

    /**
     * @return string[]
     */
    public static function getPropertyNames()
    {
        return [
            self::NAME,
            self::PSEUDONYM,
            self::FIRST_NAME,
            self::LAST_NAME,
            self::WIKI_PAGE,
        ];
    }

    /**
     * @param string $propertyName
     * @throws \Exception
     */
    private static function checkPropertyName($propertyName)
    {
        if (!in_array($propertyName, self::getPropertyNames())) {
            throw new \Exception(sprintf(
                'Bad %s property: %s',
                self::class,
                $propertyName
            ));
        }
    }
}
