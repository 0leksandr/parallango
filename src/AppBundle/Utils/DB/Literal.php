<?php

namespace AppBundle\Utils\DB;

class Literal
{
    /** @var string */
    private $stmt;

    /**
     * @param string $stmt
     */
    public function __construct($stmt)
    {
        $this->stmt = $stmt;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->stmt;
    }
}
