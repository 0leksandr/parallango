<?php

namespace Utils\DB;

class ValuesList
{
    /** @var array */
    private $values = [];

    /**
     * @param array $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }
}
