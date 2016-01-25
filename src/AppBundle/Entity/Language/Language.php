<?php

namespace AppBundle\Entity;

class Language
{
    /** @var int */
    private $id;
    /** @var string */
    private $code;

    /**
     * @param int $id
     * @param string $code
     */
    public function __construct($id, $code)
    {
        $this->id = $id;
        $this->code = $code;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}

