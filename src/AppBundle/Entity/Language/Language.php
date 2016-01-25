<?php

namespace AppBundle\Entity\Language;

use AppBundle\Entity\Identifiable;

class Language extends Identifiable
{
    /** @var string */
    private $code;

    /**
     * @param int $id
     * @param string $code
     */
    public function __construct($id, $code)
    {
        parent::__construct($id);
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}

