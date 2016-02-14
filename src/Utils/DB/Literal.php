<?php

namespace Utils\DB;

class Literal
{
    /** @var string */
    private $stmt;

    /**
     * @param string $stmt
     */
    public function __construct($stmt)
    {
        $this->stmt = (string)$stmt;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->stmt;
    }
}
